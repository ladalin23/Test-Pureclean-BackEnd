<?php

namespace App\Services;

use Auth;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

abstract class BaseService {
    protected string $statusColumn = 'active';
    protected ?string $modelLabel = null;
    protected array $statusVerbs = [
        1 => 'activated',
        0 => 'deactivated',
        2 => 'deleted',
    ];

    public function create(array $params = array())
    {
        return DB::transaction(function () use ($params) {
            return $this->getQuery()->create($params);
        });
    }


    public function update(array $params = array(), String $global_id = null)
    {
        return DB::transaction(function () use ($params, $global_id) {
            $query = $this->getQuery();
            $data = $query->where('global_id', $global_id)->first();
            if (!$data) {
                abort(404, "Data not found.");
            }
            $data->update($params);
            return $data->refresh();
        });
    }

    // Get by global_id
    public function getByGlobalId(string $modelClass, string $globalId)
    {
        $model = new $modelClass;
        $row = $model->newQuery()->where('global_id', $globalId)->first();
        if (!$row) {
            abort(404, "Data not found.");
        }
        return $row;
    }


    // Get id by global_id
    public function getIdByGlobalId($modelName, $global_id) {
        $model = new $modelName();
        $query = $model::query();

        $row = $query->where('global_id', $global_id)->first();
        if (!$row) {
            abort(404, "Data not found.");
        }
        return $row->id;
    }

    public function setStatus(string $global_id, int|bool $status)
    {
        $query  = $this->getQuery();
        $data = $query->where('global_id', $global_id)->first();
        if (!$data) {
            abort(404, "Data not found.");
        }
        // Update status column
        $data->update(['active' => $status]);
        return  $data->refresh();
    }

    public function getAll(array $params = []): LengthAwarePaginator
    {
        $query = $this->getQuery();

        // Always exclude active = 2
        $query->where('active', '!=', 2);

        // Merge request first, then override with explicit $params
        $params = array_merge(request()->all(), $params);

        // Pagination
        $limit = (int)($params['limit'] ?? 10);
        $page  = isset($params['page']) ? (int)$params['page'] : null;

        // Sorting
        $sortBy  = $params['sort_by']  ?? $params['order_column'] ?? 'created_at';
        $sortDir = strtolower($params['sort_dir'] ?? $params['order_by'] ?? 'asc');
        $sortDir = in_array($sortDir, ['asc','desc'], true) ? $sortDir : 'asc';

        // Filters & search
        $filterBy = $params['filter_by'] ?? [];
        $search   = $params['search']    ?? null;
        $columns  = $params['columns']   ?? [];

        // Eager loads
        $with      = (array)($params['with'] ?? []);
        $withCount = (array)($params['with_count'] ?? []);

        if ($with)      { $query->with($with); }
        if ($withCount) { $query->withCount($withCount); }

        // Helpers
        $isDot = fn ($s) => is_string($s) && strpos($s, '.') !== false;
        $split = fn ($s) => explode('.', $s, 2);

        /** ---------------------------
         * Filters (support dot paths)
         * -------------------------- */
        foreach ($filterBy as $column => $value) {
            if ($isDot($column)) {
                [$rel, $col] = $split($column);
                $query->whereHas($rel, function ($rq) use ($col, $value) {
                    if (is_array($value)) $rq->whereIn($col, $value);
                    else                  $rq->where($col, $value);
                });
            } else {
                if (is_array($value)) $query->whereIn($column, $value);
                else                  $query->where($column, $value);
            }
        }

        // --- Date filter (frontend sends epoch ms, UTC) ---
        $fromMs = $params['date_from'] ?? null;  // e.g. 1757264400000
        $toMs   = $params['date_to']   ?? null;  // e.g. 1757350799999

        $from = $this->msToCarbon($fromMs);
        $to   = $this->msToCarbon($toMs);
        // dd($from, $to);


        if ($from && $to) {
            // Inclusive window
            $query->whereBetween('created_at', [$from, $to]);
        } elseif ($from) {
            $query->where('created_at', '>=', $from);
        } elseif ($to) {
            $query->where('created_at', '<=', $to);
        }


        /** ---------------------------
         * Search (support dot paths)
         * -------------------------- */
        if ($search && !empty($columns)) {
            $cols = (array) $columns;
            $query->where(function ($q) use ($search, $cols, $isDot, $split) {
                foreach ($cols as $col) {
                    if ($isDot($col)) {
                        [$rel, $c] = $split($col);
                        $q->orWhereHas($rel, fn($rq) => $rq->where($c, 'like', "%{$search}%"));
                    } else {
                        $q->orWhere($col, 'like', "%{$search}%");
                    }
                }
            });
        }

        /** ---------------------------
         * Sorting (support dot paths)
         * -------------------------- */
        if ($isDot($sortBy)) {
            [$rel, $col] = $split($sortBy);
            $alias = "sort_{$rel}_" . str_replace('.', '_', $col);
            $query->withAggregate("{$rel} as {$alias}", $col)->orderBy($alias, $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        // Paginate
        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        // Keep raw params on links (avoid dumping Carbon objects)
        $paginator->appends([
            'limit'      => $limit,
            'sort_by'    => $sortBy,
            'sort_dir'   => $sortDir,
            'search'     => $search,
            'columns'    => $columns,
            'filter_by'  => $filterBy,
            'from_at'    => $from,
            'to_at'      => $to,
        ]);

        return $paginator;
    }

    private function msToCarbon($val): ?Carbon
    {
        if ($val === null || $val === '' || !is_numeric($val)) {
            return null;
        }
        // Treat as UTC instants; no tz shifting
        return Carbon::createFromTimestampMs((int) $val, 'UTC');
    }

    protected function getQuery()
    {
        return null;
    }
}
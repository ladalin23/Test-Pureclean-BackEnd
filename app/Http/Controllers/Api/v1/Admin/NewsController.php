<?php

namespace App\Http\Controllers\Api\v1\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\News;
use App\Services\BaseService;

class NewsController extends BaseAPI
{   
    private BaseService $service;
    public function __construct()
    {
        // Minimal glue: define getQuery() for Admin on the fly.
        $this->service = new class extends BaseService {
            protected function getQuery() { return News::query(); }
        };
    }

    public function index()
    {
        $news = $this->service->getAll();
        return $this->successResponse($news, 'News retrieved successfully');
    }

    public function getAllActiveNews()
    {
        $params['filter_by'] = ['active' => 1];
        $news = $this->service->getAll($params);
        return $this->successResponse($news, 'Active news retrieved successfully');
    }

    public function store(StoreNewsRequest $request)
    {
        $params = $request->validated();
        $params['admin_id'] = auth()->user()->id;
        $news = $this->service->create($params);
        return $this->successResponse($news, 'News created successfully', 201);
    }

    public function show(string $global_id)
    {
        $news = $this->service->getByGlobalId(News::class, $global_id);
        return $this->successResponse($news, 'News retrieved successfully');
    }

    public function update(UpdateNewsRequest $request, string $global_id)
    {
        $params = $request->validated();
        $news = $this->service->update($params, $global_id);
        return $this->successResponse($news, 'News updated successfully');
    }

    public function changeStatus(string $global_id, int $status)
    {
        $news = $this->service->setStatus($global_id, $status);
        return $this->successResponse($news, 'News status updated successfully');
    }
}

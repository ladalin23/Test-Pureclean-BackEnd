<?php

namespace App\Http\Controllers\Api\v1\User;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\v1\BaseAPI;
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

    public function getAllActiveNews()
    {
        $params = [];
        $params['filter_by'] = ['active' => 1];
        $news = $this->service->getAll($params);
        return $this->successResponse($news, 'Active news retrieved successfully');
    }

    public function show(string $global_id)
    {
        $news = $this->service->getByGlobalId(News::class, $global_id);
        return $this->successResponse($news, 'News retrieved successfully');
    }
}

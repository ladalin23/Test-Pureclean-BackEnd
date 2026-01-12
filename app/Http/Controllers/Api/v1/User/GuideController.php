<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Requests\StoreGuideRequest;
use App\Http\Requests\UpdateGuideRequest;
use App\Http\Controllers\Api\v1\BaseAPI;
use Illuminate\Support\Facades\DB;
use App\Services\BaseService;
use App\Models\Guide;

class GuideController extends BaseAPI
{
    private BaseService $service;
    public function __construct()
    {
        // Minimal glue: define getQuery() for Admin on the fly.
        $this->service = new class extends BaseService {
            protected function getQuery() { return Guide::query(); }
        };
    }

    public function getAllActiveGuides()
    {
        $params['filter_by'] = ['active' => 1];
        $guides = $this->service->getAll($params);
        return $this->successResponse($guides, 'Active guides retrieved successfully');
    }
}

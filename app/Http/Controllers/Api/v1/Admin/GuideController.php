<?php

namespace App\Http\Controllers\Api\v1\Admin;

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
    public function index()
    {
        $guides = $this->service->getAll();
        return $this->successResponse($guides, 'Guides retrieved successfully');
    }

    public function getAllActiveGuides()
    {
        // $params['filter_by'] = ['active' => 1];
        $guides = $this->service->getAll($params);
        return $this->successResponse($guides, 'Active guides retrieved successfully');
    }

    public function store(StoreGuideRequest $request)
    {
        $guide = $this->service->create($request->validated());
        return $this->successResponse($guide, 'Guide created successfully', 201);
    }

    public function show(string $global_id)
    {
        $guide = $this->service->getByGlobalId(Guide::class, $global_id);
        return $this->successResponse($guide, 'Guide retrieved successfully');
    }

    public function update(UpdateGuideRequest $request, string $global_id)
    {
        $updatedGuide = $this->service->update($request->validated(), $global_id);
        return $this->successResponse($updatedGuide, 'Guide updated successfully');
    }

    public function changeStatus(string $global_id, int $status)
    {
        $result = $this->service->setStatus($global_id, $status);
        return $this->successResponse($result['data'], 'Guide status updated successfully');
    }
}

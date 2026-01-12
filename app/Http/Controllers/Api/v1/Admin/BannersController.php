<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Requests\StoreBannersRequest;
use App\Http\Requests\UpdateBannersRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Models\Banners;
use App\Services\BaseService;

class BannersController extends BaseAPI
{
    private BaseService $service;
    public function __construct()
    {
        // Minimal glue: define getQuery() for Admin on the fly.
        $this->service = new class extends BaseService {
            protected function getQuery() { return Banners::query(); }
        };
    }

    public function index()
    {
        $params = [];
        $banners = $this->service->getAll($params);
        return $this->successResponse($banners, 'Banners retrieved successfully');
    }

    public function store(StoreBannersRequest $request)
    {
        $banner = $this->service->create($request->validated());
        return $this->successResponse($banner, 'Banner created successfully', 201);
    }
    
    public function getAllActiveBanners()
    {
        $params['filter_by'] = ['active' => 1];
        $banners = $this->service->getAll($params);
        return $this->successResponse($banners, 'Active banners retrieved successfully');
    }

    public function show(string $global_id)
    {
        $banner = $this->service->getByGlobalId(Banners::class, $global_id);
        return $this->successResponse($banner, 'Banner retrieved successfully');
    }

    public function update(UpdateBannersRequest $request, string $global_id)
    {
        $banner = $this->service->update($request->validated(), $global_id);
        return $this->successResponse($banner, 'Banner updated successfully');
    }

    public function changeStatus(string $global_id, int $status)
    {
        $result = $this->service->setStatus($global_id, $status);
        return $this->successResponse($result, 'Banner status updated successfully');
    }
}
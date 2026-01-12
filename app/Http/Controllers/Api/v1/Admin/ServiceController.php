<?php

namespace App\Http\Controllers\Api\v1\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Services\BaseService;
use App\Support\ServicesCache; // <-- plural, correct one


class ServiceController extends BaseAPI
{
    private BaseService $service;
    public function __construct()
    {
        // Minimal glue: define getQuery() for Admin on the fly.
        $this->service = new class extends BaseService {
            protected function getQuery() { return Service::query(); }
        };
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $params = [];
        $services = $this->service->getAll($params);
        return $this->successResponse($services, 'Services retrieved successfully');
    }

    public function getAllActiveServices()
    {
        $params['filter_by'] = ['active' => 1];
        $services = $this->service->getAll($params);
        return $this->successResponse($services, 'Active services retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request)
    {
        $params = $request->validated();
        $service = $this->service->create($params);
        ServicesCache::forget();
        return $this->successResponse($service, 'Service created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $global_id)
    {
        $service = $this->service->getByGlobalId(Service::class, $global_id);
        return $this->successResponse($service, 'Service retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, string $global_id)
    {
        $params = $request->validated();
        $updatedService = $this->service->update($global_id, $params);
        ServicesCache::forget();
        return $this->successResponse($updatedService, 'Service updated successfully');
    }

    public function changeStatus(string $global_id, int $status)
    {
        $result = $this->service->setStatus($global_id, $status);
        ServicesCache::forget();
        return $this->successResponse($result, 'Service status updated successfully');
    }
}
<?php

namespace App\Http\Controllers\Api\v1\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Services\BaseService;

class BranchController extends BaseAPI
{
    private BaseService $service;
    public function __construct()
    {
        // Minimal glue: define getQuery() for Admin on the fly.
        $this->service = new class extends BaseService {
            protected function getQuery() { return Branch::query(); }
        };
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = $this->service->getAll();
        return $this->successResponse($branches, 'Branches retrieved successfully');
    }

    public function getAllActiveBranches()
    {
        $params['filter_by'] = ['active' => 1];
        $branches = $this->service->getAll($params);
        return $this->successResponse($branches, 'Active branches retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBranchRequest $request)
    {
        $params = $request->validated();
        $branch = $this->service->create($params);
        return $this->successResponse($branch, 'Branch created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $global_id)
    {
        $branch = $this->service->getByGlobalId(Branch::class, $global_id);
        return $this->successResponse($branch, 'Branch retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBranchRequest $request, string $global_id)
    {
        $params = $request->validated();
        $branch = $this->service->update($params, $global_id);
        return $this->successResponse($branch, 'Branch updated successfully');
    }

    public function changeStatus(string $global_id, int $status)
    {
        $result = $this->service->setStatus($global_id, $status);
        return $this->successResponse($result, 'Branch status updated successfully');
    }

}

<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\Admin;
use App\Models\Branch;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Http\Controllers\Api\v1\BaseAPI;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateStatusRequest;
use App\Services\BaseService;

class AdminController extends BaseAPI
{
    private BaseService $service;
    public function __construct()
    {
        // Minimal glue: define getQuery() for Admin on the fly.
        $this->service = new class extends BaseService {
            protected function getQuery() { return Admin::query(); }
        };
    }

    public function index()
    {
        $params = [];
        $data = $this->service->getAll($params);
        return $this->successResponse($data, 'Admins retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminRequest $request)
    {
        $params = $request->validated();
        $branch_id = $this->service->getIdByGlobalId(Branch::class, $params['branch_id']);
        $params['branch_id'] = $branch_id;
        $data = $this->service->create($params);
        return $this->successResponse($data, 'Admin created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($global_id)
    {
        $data = $this->service->getByGlobalId(Admin::class, $global_id);
        return $this->successResponse($data, 'Admin retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */ 
    
    public function update(UpdateAdminRequest $request, $global_id)
    {
        $params = $request->validated();   // <-- Use validated, not only()
        $data = $this->service->update($params, $global_id);
        return $this->successResponse($data, 'Admin updated successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    
    public function changeStatus(string $global_id, int $status)
    {
        $data = $this->service->setStatus($global_id, $status);
        return $this->successResponse($data, 'Admin status updated successfully');
    }
    

}

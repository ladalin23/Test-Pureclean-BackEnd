<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Api\v1\BaseAPI;
use App\Models\service;
use Illuminate\Support\Facades\DB;
use App\Services\ServiceSV;
class ServiceController extends BaseAPI
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
        $params['filter_by'] = ['active' => 1];
        $services = $this->service->getAll($params);
        return $this->successResponse($services, 'Services retrieved successfully');
    }
}

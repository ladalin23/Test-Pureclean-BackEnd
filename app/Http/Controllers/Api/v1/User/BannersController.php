<?php

namespace App\Http\Controllers\Api\v1\User;

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
        // Minimal glue: define getQuery() for User on the fly.
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
}
<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Services\BaseService;
use App\Support\SettingsCache; // <-- plural, correct one

class SettingController extends BaseAPI
{
    private BaseService $service;
    public function __construct()
    {
        // Minimal glue: define getQuery() for Admin on the fly.
        $this->service = new class extends BaseService {
            protected function getQuery() { return Setting::query(); }
        };
    }

    public function index()
    {
        $settings = $this->service->getAll();
        return $this->successResponse($settings, 'Settings retrieved successfully');
    }

    public function getAllActiveSettings()
    {
        $params['filter_by'] = ['active' => 1];
        $settings = $this->service->getAll($params);
        return $this->successResponse($settings, 'Active settings retrieved successfully');
    }

    public function store(StoreSettingRequest $request)
    {
        $params = $request->validated();
        $setting = $this->service->create($params);
        SettingsCache::forget();
        return $this->successResponse($setting, 'Setting created successfully');
    }

    public function show(string $global_id)
    {
        $setting = $this->service->getByGlobalId(Setting::class, $global_id);
        return $this->successResponse($setting, 'Setting retrieved successfully');
    }

    public function update(UpdateSettingRequest $request, string $global_id)
    {
        $params = $request->validated();

        $setting = $this->service->update($params, $global_id);
        SettingsCache::forget();
    }

    public function changeStatus(string $global_id, int $status)
    {
        $setting = $this->service->setStatus($global_id, $status);
        SettingsCache::forget();
        return $this->successResponse($setting, 'Setting status changed successfully');
    }
}

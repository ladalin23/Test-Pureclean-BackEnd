<?php

namespace App\Observers;

use App\Models\Service;
use Illuminate\Support\Facades\Cache;

class ServiceObserver
{
    public function created(Service $service) { Cache::forget('services.all'); }
    public function updated(Service $service) { Cache::forget('services.all'); }
    public function deleted(Service $service) { Cache::forget('services.all'); }
}

<?php

namespace App\Http\Controllers\Api\v1\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Requests\UpdateFeedbackRequest;
use App\Models\Feedback;
use App\Services\BaseService;
class FeedbackController extends BaseAPI
{
    private BaseService $service;
    public function __construct()
    {
        // Minimal glue: define getQuery() for Admin on the fly.
        $this->service = new class extends BaseService {
            protected function getQuery() { return Feedback::query(); }
        };
    }

    public function index()
    {
        $feedbacks = $this->service->getAll();
        return $this->successResponse($feedbacks, 'Feedbacks retrieved successfully');
    }

    public function show(string $global_id)
    {
        $feedback = $this->service->getByGlobalId(Feedback::class, $global_id);
        return $this->successResponse($feedback, 'Feedback retrieved successfully');
    }
}

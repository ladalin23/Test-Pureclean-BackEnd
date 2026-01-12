<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Controllers\Api\v1\BaseAPI;
use Illuminate\Support\Facades\DB;
use App\Services\BaseService;
use App\Models\Product;
class ProductController extends BaseAPI
{
    private BaseService $service;
    public function __construct()
    {
        // Minimal glue: define getQuery() for Admin on the fly.
        $this->service = new class extends BaseService {
            protected function getQuery() { return Product::query(); }
        };
    }

    public function index()
    {
        $products = $this->service->getAll();
        return $this->successResponse($products, 'Products retrieved successfully');
    }

    public function getAllActiveProducts()
    {
        $params['filter_by'] = ['active' => 1];
        $products = $this->service->getAll($params);
        return $this->successResponse($products, 'Active products retrieved successfully');
    }

    public function store(StoreProductRequest $request)
    {
        $product = $this->service->create($request->validated());
        return $this->successResponse($product, 'Product created successfully', 201);
    }

    public function show(string $global_id)
    {
        $product = $this->service->getByGlobalId(Product::class, $global_id);
        return $this->successResponse($product, 'Product retrieved successfully');
    }

    public function update(UpdateProductRequest $request, string $global_id)
    {
        $updatedProduct = $this->service->update($request->validated(), $global_id);
        return $this->successResponse($updatedProduct, 'Product updated successfully');
    }

    public function changeStatus(string $global_id, int $status)
    {
        $result = $this->service->setStatus($global_id, $status);
        return $this->successResponse($result['data'], 'Product status updated successfully');
    }
}
<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Requests\StorePurchasedRequest;
use App\Http\Requests\UpdatePurchasedRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Models\Purchased;
use App\Services\PurchasedSV;
use App\Services\LoyaltyCardSV;

class PurchasedController extends BaseAPI
{
    protected $purchasedSV, $loyaltyCardSV;

    public function __construct()
    {
        $this->purchasedSV = new PurchasedSV();
        $this->loyaltyCardSV = new LoyaltyCardSV();
    }

    public function index()
    {
        $purchasedItems = $this->purchasedSV->getAllPurchased(1);
        return $this->successResponse($purchasedItems, 'Purchased items retrieved successfully');
    }

    public function store(StorePurchasedRequest $request)
    {
        $params = $request->validated();
        $purchasedItem = $this->purchasedSV->createPurchased($params)->refresh();
        $this->loyaltyCardSV->attachPurchase($purchasedItem);
        return $this->successResponse($purchasedItem, 'Purchased item created successfully');
    }

    public function show(string $global_id)
    {
        $purchased = $this->purchasedSV->getPurchased($global_id);
        return $this->successResponse($purchased, 'Purchased item retrieved successfully');
    }

    public function update(UpdatePurchasedRequest $request, string $global_id)
    {
        $params = $request->validated();
        $updatedItem = $this->purchasedSV->updatePurchased($global_id, $params);
        return $this->successResponse($updatedItem, 'Purchased item updated successfully');
    }
}

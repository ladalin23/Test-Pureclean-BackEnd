<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRewardRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,global_id',
            'reward_type'  => 'required|string|in:first,second',
            'product_id' => 'required_if:reward_type,first|nullable|exists:products,global_id',
            'loyalty_card_id' => 'required|exists:loyalty_cards,global_id'
        ];
    }
}

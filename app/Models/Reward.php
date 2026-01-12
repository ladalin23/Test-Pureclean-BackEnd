<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidTrait;

class Reward extends Model
{
    /** @use HasFactory<\Database\Factories\RewardFactory> */
    use HasFactory, UuidTrait;
    protected $keyType = 'string';
    protected $primaryKey = 'global_id';
    protected $fillable = [
        'branch_id',
        'user_id',
        'admin_id',
        'product_id',
        'service_id',
        'qty',
        'active'
    ];

    // ── RELATIONSHIPS ─────────────────────────────────────────────
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // If "admin" is also users table, this is fine:
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');    
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
    // If a reward corresponds to exactly one loyalty card via second_reward_id
    public function loyaltyCard()
    {
        // loyalty_cards.second_reward_id -> rewards.id
        return $this->hasOne(LoyaltyCard::class, 'second_reward_id', 'id');
    }

    // If you also need the 'first' link, you can add:
    public function loyaltyCardAsFirst()
    {
        return $this->hasOne(LoyaltyCard::class, 'first_reward_id', 'id');
    }
}

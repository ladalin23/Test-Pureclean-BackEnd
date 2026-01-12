<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidTrait;

class Purchased extends Model
{
    /** @use HasFactory<\Database\Factories\PurchasedFactory> */
    use HasFactory, UuidTrait;
    protected $keyType = 'string';
    protected $primaryKey = 'global_id';
    protected $fillable = [
        'user_id',
        'branch_id',
        'admin_id',
        'service_id',
        'status',
        'det',
        'sft',
        'acn',
        'det_price',
        'sft_price',
        'acn_price',
        'service_price',
        'is_gift',
        'payment_method',
        'total_price',
        'contact',
        'active',
    ];

    // ── RELATIONSHIPS ─────────────────────────────────────────────
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id')
            ->select(['id','global_id','name']);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
            ->select(['id','global_id','username','u_id']);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id')
            ->select(['id','global_id','username']);
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id')
            ->select(['id','global_id','name']);
    }
}

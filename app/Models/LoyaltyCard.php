<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyCard extends Model
{
    use HasFactory;

    protected $primaryKey = 'global_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'global_id',
        'user_id',
        'points',
        'purchase1_id','purchase2_id','purchase3_id','purchase4_id','purchase5_id',
        'purchase6_id','purchase7_id', 'purchase8_id', 'purchase9_id', 'purchase10_id', 'purchase11_id',
        'first_reward_id','second_reward_id',
        'expires_at','active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'active'     => 'boolean',
        'points'     => 'integer',
    ];

    // Optional: use global_id in route model binding
    public function getRouteKeyName(): string
    {
        return 'global_id';
    }

    // ── PURCHASE RELATIONS ────────────────────────────
    public function purchase1()
    {
        return $this->belongsTo(Purchased::class, 'purchase1_id', 'id')
            ->select(['id','service_id','status'])
            ->with('service:id,name');
    }

    public function purchase2()
    {
        return $this->belongsTo(Purchased::class, 'purchase2_id', 'id')
            ->select(['id','global_id','service_id','status'])
            ->with('service:id,global_id,name');
    }

    public function purchase3()
    {
        return $this->belongsTo(Purchased::class, 'purchase3_id', 'id')
            ->select(['id','global_id','service_id','status'])
            ->with('service:id,global_id,name');
    }

    public function purchase4()
    {
        return $this->belongsTo(Purchased::class, 'purchase4_id', 'id')
            ->select(['id','global_id','service_id','status'])
            ->with('service:id,global_id,name');
    }

    public function purchase5()
    {
        return $this->belongsTo(Purchased::class, 'purchase5_id', 'id')
            ->select(['id','global_id','service_id','status'])
            ->with('service:id,global_id,name');
    }

    public function purchase6()
    {
        return $this->belongsTo(Purchased::class, 'purchase6_id', 'id')
            ->select(['id','global_id','service_id','status'])
            ->with('service:id,global_id,name');
    }

    public function purchase7()
    {
        return $this->belongsTo(Purchased::class, 'purchase7_id', 'id')
            ->select(['id','global_id','service_id','status'])
            ->with('service:id,global_id,name');
    }

    public function purchase8()
    {
        return $this->belongsTo(Purchased::class, 'purchase8_id', 'id')
            ->select(['id','global_id','service_id','status'])
            ->with('service:id,global_id,name');
    }

    public function purchase9()
    {
        return $this->belongsTo(Purchased::class, 'purchase9_id', 'id')
            ->select(['id','global_id','service_id','status'])
            ->with('service:id,global_id,name');
    }

    public function purchase10()
    {
        return $this->belongsTo(Purchased::class, 'purchase10_id', 'id')
            ->select(['id','global_id','service_id','status'])
            ->with('service:id,global_id,name');
    }

    public function purchase11()
    {
        return $this->belongsTo(Purchased::class, 'purchase11_id', 'id')
            ->select(['id','global_id','service_id','status'])
            ->with('service:id,global_id,name');
    }

    // reward relations if you want their names
    // public function firstReward()  { return $this->belongsTo(Service::class, 'first_reward_id', 'id'); }
    // public function secondReward() { return $this->belongsTo(Service::class, 'second_reward_id', 'id'); }

    // Optional relations you use frequently
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Handy scopes
    public function scopeActive($q)     { return $q->where('active', 1); }
    public function scopeNotDeleted($q) { return $q->where('active', '!=', 2); }
}

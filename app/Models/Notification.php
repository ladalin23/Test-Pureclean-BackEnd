<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidTrait;

class Notification extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationFactory> */
    use HasFactory, UuidTrait;
    protected $keyType = 'string';
    protected $primaryKey = 'global_id';
    protected $fillable = [
        'image_url',
        'title',
        'body',
        'data',
        'admin_id',
        'user_ids',
        'topic',
        'active',
    ];
    protected $casts = [
        'user_ids' => 'array',
        'active' => 'boolean',
    ];
}

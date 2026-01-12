<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidTrait;

class News extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory, UuidTrait;
    protected $keyType = 'string';
    protected $primaryKey = 'global_id';
    protected $fillable = [
        'admin_id',
        'title',
        'content',
        'image_url',
        'active',
    ];


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidTrait;

class Feedback extends Model
{
    /** @use HasFactory<\Database\Factories\FeedbackFactory> */
    use HasFactory, UuidTrait;
    protected $keyType = 'string';
    protected $primaryKey = 'global_id';
    protected $fillable = [
        'rating',
        'message',
    ];


}

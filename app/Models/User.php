<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\UuidTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, UuidTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    // UUID primary key
    protected $keyType = 'string';
    protected $primaryKey = 'global_id';

    protected $fillable = [
        'u_id',
        'username',
        'hash',
        'email',
        'password',
        'phone',
        'profile_picture',
        'gender',
        'dob',
        'otp',
        'otp_expires_at',
        'google_id',
        'telegram_id',
        'is_verify_email',
        'is_verify_phone',
        'active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Get the maximum u_id currently in the table
            $maxUId = DB::table('users')->max('u_id');

            // If no u_id exists yet, start from 1001, otherwise increment the max
            $user->u_id = $maxUId ? $maxUId + 1 : 1001;
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function deviceTokens()
    {
        return $this->hasMany(\App\Models\DeviceToken::class);
    }

 /**
     * Get the identifier that will be stored in the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}

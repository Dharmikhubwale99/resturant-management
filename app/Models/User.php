<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'restaurant_id',
        'name',
        'mobile',
        'email',
        'password',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'is_active',
        'pin_code_id',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'pincode_id' => 'integer',
            'otp' => 'integer',
            'otp_expires_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'pin_code_id' => 'integer',
        ];
    }

    public function pinCode(): BelongsTo
    {
        return $this->belongsTo(PinCode::class);
    }

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }

    public function policies(): HasMany
    {
        return $this->hasMany(Policy::class);
    }

     protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            do {
                $refer_code = Str::upper(Str::random(6));
            } while (self::where('refer_code', $refer_code)->exists());

            $user->refer_code = $refer_code;

            return $user;
        });
    }

    public function getRoleAttribute()
    {
        return $this->roles()->first()->name;
    }

    public function setting()
    {
        return $this->hasOne(Setting::class);
    }

}

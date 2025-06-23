<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Model
{
    use HasFactory, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'pincode_id',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'is_active',
        'pin_code_id',
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
}

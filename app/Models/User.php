<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'shop_id',
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    /**
     * Shop this user belongs to.
     */
    public function shop(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Determine if the user has owner privileges (full access).
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Determine if the user has staff privileges (restricted access).
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }
}

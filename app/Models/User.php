<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'business_id', 'name', 'username', 'email', 'phone',
        'avatar', 'role', 'status', 'password', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'password'          => 'hashed',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function isOwner(): bool        { return $this->role === 'owner'; }
    public function isRepairPerson(): bool { return $this->role === 'repair_person'; }
    public function isSalesPerson(): bool  { return $this->role === 'sales_person'; }
}
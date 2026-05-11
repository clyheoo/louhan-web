<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\PasswordHistory;

class User extends Authenticatable
{   
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'plain_password',
    ];

    public function isAdmin() { return $this->role === 'admin'; }
    public function isJuri() { return $this->role === 'juri'; }
    public function isGrandJuri() { return $this->role === 'grand_juri'; }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class)->orderByDesc('created_at');
    }
}
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Althinect\FilamentSpatieRolesPermissions\Concerns\HasSuperAdmin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles,HasSuperAdmin;

    protected $fillable = [
        'name',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // public function canAccessPanel(\Filament\Panel $panel): bool
    // {
    //     if ($panel->getId() === 'partner') {
    //         return $this->hasRole('shirkat_admin') 
    //             || $this->hasRole('shirkat_operator');
    //     }

    //     if ($panel->getId() === 'admin') {
    //         return $this->hasRole('super_admin') 
    //             || $this->hasRole('ministry_operator');
    //     }

    //     return false;
    // }

}

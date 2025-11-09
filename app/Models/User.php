<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'titulaire',
        'email',
        'password',
        'role',
    ];
   

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    

     public function client():HasOne{
        return $this->hasOne(Client::class);
    }

    public function admin():HasOne{
        return $this->hasOne(Admin::class);
    }

    public function getRoleAttribute()
    {
        // Vérifier d'abord si le rôle est défini directement dans la base de données
        if (!empty($this->attributes['role'])) {
            return $this->attributes['role'];
        }

        // Sinon, déterminer le rôle basé sur les relations
        if($this->client)
        {
            return 'client';

        }else if($this->admin)
        {
            return 'admin';
        }

        return null;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }



}

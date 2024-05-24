<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'wpu6_users';

    protected $fillable = [
        'parent',
        'user_login',
        'user_pass',
        'user_nicename',
        'user_email',
        'user_url',
        'user_registered',
        'user_activation_key',
        'user_type',
        'user_status',
        'display_name',
        'spam',
        'deleted',
        'otp',
        'verified_at',
    ];

    public function userdetails()
    {
        return $this->hasMany(UserDetails::class, 'user_id', 'ID')->whereNull('deleted_at');
    }
    
    public function rmsmdetails()
    {
        return $this->hasMany(RmDonation::class, 'member_id', 'ID')->whereNull('deleted_at')->orderByDesc('id');
    }

}

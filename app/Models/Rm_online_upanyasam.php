<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Rm_online_upanyasam extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     protected $table = 'rm_online_upanyasam';

    protected $fillable = [
        'category_id',
        'post_title',
        'post_date_gmt',
        'post_slug',
        'post_content',
        'download_url',
        'post_type',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

}

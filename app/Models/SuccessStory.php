<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuccessStory extends Model
{
    use HasApiTokens, HasFactory, SoftDeletes, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     protected $table = 'successstory';

    protected $fillable = [
        'weddingphoto',
        'bridename',
        'brideid',
        'groomname',
        'groomid',
        'email',
        'mobile',
        'address',
        'marriagedate',
        'successmessage',
        'approve',
    ];

    protected $dates = ['deleted_at'];

}

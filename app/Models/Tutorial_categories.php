<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Tutorial_categories extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'tutorial_categories';

    protected $fillable = [
        'category_name',
        'category_type',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function audios()
    {
        return $this->hasMany(Course_lesson::class, 'audio_category_id', 'id')->where('post_type', 'LIKE', 'tutorial-audio')->whereNull('deleted_at');
    }

    public function videos()
    {
        return $this->hasMany(Course_lesson::class, 'audio_category_id', 'id')->where('post_type', 'LIKE', 'tutorial-video')->whereNull('deleted_at');
    }

    public function upanyasamaudios()
    {
        return $this->hasMany(Rm_online_upanyasam::class, 'category_id', 'id')->where('structure_type', '=', 'audio')->whereNull('deleted_at');
    }

    public function upanyasamvideos()
    {
        return $this->hasMany(Rm_online_upanyasam::class, 'category_id', 'id')->where('structure_type', '=', 'video')->whereNull('deleted_at');
    }

}

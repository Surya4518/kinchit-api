<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use DB;

class UserDetails extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'userprofile_dt';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'last_name_2',
        'dob',
        'gender',
        'acharyan',
        'user_email',
        'landline_ph_no',
        'phone_number',
        'phone_number_wa',
        'address_1',
        'address_2',
        'area',
        'city',
        'state',
        'postcode',
        'country',
        'native',
        'kinchit_enpani',
        'payment_method',
        'tech_savvy',
        'listen_cd',
        'rm_method',
        'form_signed_date',
        'form_signed_place',
        'family_members',
        'family_details',
        'volunteer_ques_1',
        'volunteer_ques_2',
        'volunteer_ques_3',
        'volunteer_ques_4',
        'qualification',
        'hobbies',
        'skills',
        'work_experience',
        'user_image',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

}

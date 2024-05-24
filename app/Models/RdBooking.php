<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RdBooking extends Model
{
    use HasApiTokens, HasFactory, SoftDeletes, Notifiable;

    protected $table = 'rd_booking';

    protected $fillable = [
        'booker_name',
        'booker_lastname',
        'no_of_people',
        'people_names',
        'divyadesam',
        'date_from',
        'date_to',
        'phone_no',
        'email',
        'address_1',
        'address_2',
        'city',
        'state',
        'pincode',
        'country',
        'kkt_or_not',
        'membership_id',
    ];

    // Add any relationships or additional configuration here

    protected $dates = ['deleted_at'];
}

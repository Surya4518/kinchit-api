<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmDelivery extends Model
{
    protected $table = 'deposit_details';
    use HasFactory;
    
    public function user()
    {
        return $this->hasMany(User::class, 'ID', 'user_id')->whereNull('deleted_at');
    }
    public function userdetails()
    {
        return $this->hasMany(UserDetails::class, 'user_id', 'user_id')->whereNull('deleted_at');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    //
    protected $fillable = [
        'inventaris_id',
        'user_id',
        'date_borrow',
        'status',
        'date_back'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    protected $fillable = [
        'name',
        'image',
        'specification',
        'condition',
        'status',
        'qr_link'
    ];

}

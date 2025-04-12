<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventaris extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'serial_number',
        'type',
        'quantity',
        'condition',
        'location',
        'image',
        'qr_link'
    ];

    public function borrow(){
        return $this->hasMany(Borrow::class);
    }

    public function groupInventaris()
    {
        return $this->hasMany(GroupInventaris::class);
    }
}

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
        'broken_description',
        'status',
        'qr_link',
        'location',
    ];

    public function borrow(){
        return $this->hasMany(Borrow::class);
    }
}

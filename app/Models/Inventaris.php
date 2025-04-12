<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventaris extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'image',
        'specification',
        'condition',
        'broken_description',
        'status',
        'qr_link',
        'location',
        'quantity'
    ];

    public function borrow(){
        return $this->hasMany(Borrow::class);
    }

    public function groupInventaris()
    {
        return $this->hasMany(GroupInventaris::class);
    }
}

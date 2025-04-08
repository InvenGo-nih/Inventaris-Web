<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Borrow extends Model
{
    
    use SoftDeletes;

    protected $fillable = [
        'inventaris_id',
        'borrow_by',
        'quantity',
        'date_borrow',
        'status',
        'date_back'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class);
    }
}

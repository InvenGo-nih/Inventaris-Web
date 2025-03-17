<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    //
    protected $fillable = [
        'inventaris_id',
        'borrow_by',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Borrow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'borrow_by',
        'inventaris_id',
        'quantity',
        'date_borrow',
        'date_back',
        'max_return_date',
        'status',
        'img_borrow',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'date_borrow' => 'date',
        'date_back' => 'date',
        'max_return_date' => 'date',
    ];

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class);
    }
}

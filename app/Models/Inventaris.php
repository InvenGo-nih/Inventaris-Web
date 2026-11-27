<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventaris extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_inventaris_id',
        'name',
        'specification',
        'serial_number',
        'type',
        'quantity',
        'condition',
        'status',
        'location',
        'image',
        'broken_description',
        'is_borrow',
        'qr_link',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_borrow' => 'boolean',
    ];

    public function borrow()
    {
        return $this->hasMany(Borrow::class);
    }

    public function groupInventaris()
    {
        return $this->belongsTo(GroupInventaris::class, 'group_inventaris_id');
    }

    public function group()
    {
        return $this->groupInventaris();
    }
}

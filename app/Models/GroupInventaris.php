<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInventaris extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'image',
    ];

    public function inventaris()
    {
        return $this->hasMany(Inventaris::class, 'group_inventaris_id');
    }
}

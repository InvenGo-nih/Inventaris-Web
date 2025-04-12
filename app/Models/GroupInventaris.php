<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupInventaris extends Model
{
    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class);
    }
}

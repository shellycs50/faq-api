<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Language extends Model
{
    use HasFactory;

    public function topics() : HasMany
    {
        return $this->hasMany(Topic::class);
    }

}

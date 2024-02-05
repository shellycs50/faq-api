<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Answer;


class Question extends Model
{
    protected $fillable = [
        'content',
        'answered',
        'language_id',
        'topic_id',
    ];
    use HasFactory;

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'type',
        'stage',
        'length',
        'width',
        'area',
        'description',
        'firebase_uid',
    ];

    public function details()
    {
        return $this->hasMany(HouseDetail::class);
    }

    public function ideaBooks()
    {
        return $this->hasMany(IdeaBook::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdeaBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'description','house_id'
    ];

    public function ideas()
    {
        return $this->hasMany(Idea::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }

}

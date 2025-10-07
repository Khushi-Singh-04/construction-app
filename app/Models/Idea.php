<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Idea extends Model
{
    use HasFactory;

    protected $fillable = [
        'idea_book_id', 'user_id', 'type', 'image_path','is_approved','notes'
    ];

    public function ideaBook()
    {
        return $this->belongsTo(IdeaBook::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

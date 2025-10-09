<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProgressCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'name',
    ];

    public function works()
    {
        return $this->hasMany(DailyProgressWork::class, 'category_id');
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function getCoverImageAttribute()
    {
        $image = \App\Models\DailyProgressWorkerImage::whereHas('worker.work', function ($query) {
            $query->where('category_id', $this->id);
        })
        ->latest('created_at')
        ->first();

        return $image ? $image->image_path : null;
    }
}

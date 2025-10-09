<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProgressWork extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'date_of_work',
        'total_workers',
    ];

    public function category()
    {
        return $this->belongsTo(DailyProgressCategory::class, 'category_id');
    }


    public function workers()
    {
        return $this->hasMany(DailyProgressWorker::class, 'work_id');
    }

}

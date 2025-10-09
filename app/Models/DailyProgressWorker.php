<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProgressWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'name',
        'description',
        'date_of_work'
    ];

    public function work()
    {
        return $this->belongsTo(DailyProgressWork::class, 'work_id');
    }

    public function images()
    {
        return $this->hasMany(DailyProgressWorkerImage::class, 'worker_id');
    }
}

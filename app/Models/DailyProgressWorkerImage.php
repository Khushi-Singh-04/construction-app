<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProgressWorkerImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'image_path'
    ];

    public function worker()
    {
        return $this->belongsTo(DailyProgressWorker::class, 'worker_id');
    }
}

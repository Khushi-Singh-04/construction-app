<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyProgressWork;
use App\Models\DailyProgressWorker;
use App\Models\DailyProgressWorkerImage;

class DailyProgressWorkController extends Controller
{
    // List all workers for a work
public function listWorkers($workId)
{
    $work = DailyProgressWork::find($workId);

    if (!$work) {
        return response()->json(['message' => 'Work not found'], 404);
    }

    $workers = $work->workers()->get(['id', 'name']);

    return response()->json([
        'message' => 'Workers fetched successfully',
        'data' => $workers
    ]);
}

    // Get work details uploaded by a specific worker
public function workerWorkDetails($workId, $workerId)
{
    $worker = DailyProgressWorker::with('images')
        ->where('work_id', $workId)
        ->find($workerId);

    if (!$worker) {
        return response()->json(['message' => 'Worker or work not found'], 404);
    }

    return response()->json([
        'message' => 'Work details fetched successfully',
        'data' => [
            'worker_id' => $worker->id,
            'name' => $worker->name,
            'description' => $worker->description,
            'date_of_work' => $worker->date_of_work ?? $worker->created_at->format('Y-m-d'),
            'images' => $worker->images->map(fn($img) => ['id' => $img->id, 'image_path' => $img->image_path])
        ]
    ]);
}

}

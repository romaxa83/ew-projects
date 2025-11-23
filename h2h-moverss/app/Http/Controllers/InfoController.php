<?php

namespace App\Http\Controllers;

use App\Models\Tasks\Task;
use Illuminate\Http\{JsonResponse};

class InfoController extends Controller
{
    public function getInfo(): JsonResponse
    {
        $user = auth()->user();

        $countTask = 0;

        if($user){
            $countTask = Task::query()
                ->where('executor_id', $user->id)
                ->where('is_read', 0)
                ->count();
        }

        return response()
            ->json([
                'count_task_for_notify' => $countTask,
            ]);
    }
}

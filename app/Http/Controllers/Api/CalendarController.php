<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function getTasks(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $user = Auth::user();
        
        $todos = $user->todos()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->with(['list', 'tags'])
            ->get();
        
        // Group by date for calendar display
        $tasksByDate = [];
        foreach ($todos as $todo) {
            $date = Carbon::parse($todo->due_date)->format('Y-m-d');
            if (!isset($tasksByDate[$date])) {
                $tasksByDate[$date] = [];
            }
            $tasksByDate[$date][] = [
                'id' => $todo->id,
                'title' => $todo->title,
                'description' => $todo->description,
                'status' => $todo->status,
                'priority' => $todo->priority,
                'due_date' => $todo->due_date,
                'list_id' => $todo->list_id,
                'list_name' => $todo->list ? $todo->list->name : null,
            ];
        }
        
        return response()->json([
            'month' => $month,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'tasksByDate' => $tasksByDate,
            'tasks' => $todos->map(function($todo) {
                return [
                    'id' => $todo->id,
                    'title' => $todo->title,
                    'description' => $todo->description,
                    'status' => $todo->status,
                    'priority' => $todo->priority,
                    'due_date' => $todo->due_date,
                ];
            })
        ]);
    }
    
    public function createTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:pending,in_progress,completed',
            'list_id' => 'nullable|exists:todo_lists,id',
        ]);
        
        $user = Auth::user();
        
        $todo = $user->todos()->create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'priority' => $request->priority ?? 'medium',
            'status' => $request->status ?? 'pending',
            'list_id' => $request->list_id,
            'sort_order' => 0,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'task' => $todo
        ]);
    }
    
    public function rescheduleTask(Request $request, $id)
    {
        $todo = Todo::findOrFail($id);
        
        $this->authorize('update', $todo);
        
        $request->validate([
            'due_date' => 'required|date',
        ]);
        
        $todo->update([
            'due_date' => $request->due_date,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Task rescheduled successfully',
            'task' => $todo
        ]);
    }
    
    public function getEvents(Request $request)
    {
        $startDate = $request->get('start', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end', now()->endOfMonth()->toDateString());
        
        $user = Auth::user();
        
        $todos = $user->todos()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->with(['list'])
            ->get();
        
        $events = $todos->map(function($todo) {
            $priorityColors = [
                'low' => '#10b981',
                'medium' => '#f59e0b',
                'high' => '#ef4444',
                'urgent' => '#dc2626',
            ];
            
            return [
                'id' => $todo->id,
                'title' => $todo->title,
                'start' => $todo->due_date->format('Y-m-d'),
                'color' => $priorityColors[$todo->priority] ?? '#667eea',
                'extendedProps' => [
                    'status' => $todo->status,
                    'priority' => $todo->priority,
                    'description' => $todo->description,
                    'list_id' => $todo->list_id,
                    'list_name' => $todo->list ? $todo->list->name : null,
                ]
            ];
        });
        
        return response()->json($events);
    }
}

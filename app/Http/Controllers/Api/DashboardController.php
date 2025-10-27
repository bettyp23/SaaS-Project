<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function stats()
    {
        $user = Auth::user();
        
        // Get todo statistics
        $totalTodos = $user->todos()->count();
        $completedTodos = $user->todos()->where('status', 'completed')->count();
        $inProgressTodos = $user->todos()->where('status', 'in_progress')->count();
        $pendingTodos = $user->todos()->where('status', 'pending')->count();
        
        // Calculate completion rate
        $completionRate = $totalTodos > 0 ? round(($completedTodos / $totalTodos) * 100) : 0;
        
        // Priority distribution breakdown
        $priorityDistribution = [
            'low' => $user->todos()->where('priority', 'low')->count(),
            'medium' => $user->todos()->where('priority', 'medium')->count(),
            'high' => $user->todos()->where('priority', 'high')->count(),
            'urgent' => $user->todos()->where('priority', 'urgent')->count(),
        ];
        
        // Overdue tasks
        $overdueTasks = $user->todos()
            ->whereNotNull('due_date')
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', now())
            ->count();
        
        // Get recent todos
        $recentTodos = $user->todos()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Weekly productivity trend (last 7 days)
        $productivityTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayName = now()->subDays($i)->format('D');
            $count = $user->todos()
                ->where('status', 'completed')
                ->whereDate('completed_at', $date)
                ->count();
            
            $productivityTrend[] = [
                'date' => $date,
                'day' => $dayName,
                'count' => $count
            ];
        }
        
        return response()->json([
            'totalTodos' => $totalTodos,
            'completedTodos' => $completedTodos,
            'inProgressTodos' => $inProgressTodos,
            'pendingTodos' => $pendingTodos,
            'completionRate' => $completionRate,
            'priorityDistribution' => $priorityDistribution,
            'overdueTasks' => $overdueTasks,
            'recentTodos' => $recentTodos->map(function($todo) {
                return [
                    'id' => $todo->id,
                    'title' => $todo->title,
                    'status' => $todo->status,
                    'priority' => $todo->priority,
                    'due_date' => $todo->due_date,
                    'created_at' => $todo->created_at,
                ];
            }),
            'productivityTrend' => $productivityTrend,
            'timestamp' => now()->toIso8601String()
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Get todo statistics
        $totalTodos = $user->todos()->count();
        $completedTodos = $user->todos()->where('status', 'completed')->count();
        $inProgressTodos = $user->todos()->where('status', 'in_progress')->count();
        $pendingTodos = $user->todos()->where('status', 'pending')->count();
        
        // Get recent todos
        $recentTodos = $user->todos()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('dashboard', compact(
            'totalTodos',
            'completedTodos',
            'inProgressTodos',
            'pendingTodos',
            'recentTodos'
        ));
    }
}

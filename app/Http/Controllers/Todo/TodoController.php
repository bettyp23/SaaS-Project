<?php

namespace App\Http\Controllers\Todo;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use App\Models\TodoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Auth::user()->todos()->with('list')->withTrashed(false);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('list_id')) {
            $query->where('list_id', $request->list_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $todos = $query->orderBy('sort_order')->get();
        $lists = Auth::user()->todoLists()->get();

        return view('todos.index', compact('todos', 'lists'));
    }

    public function create()
    {
        $lists = Auth::user()->todoLists()->get();
        return view('todos.create', compact('lists'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'list_id' => 'nullable|exists:todo_lists,id',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $todo = Auth::user()->todos()->create([
            'title' => $request->title,
            'description' => $request->description,
            'list_id' => $request->list_id,
            'priority' => $request->priority ?? 'medium',
            'due_date' => $request->due_date,
            'status' => 'pending',
        ]);

        return redirect()->route('tasks.index')->with('success', 'Todo created successfully.');
    }

    public function show(Todo $todo)
    {
        $this->authorize('view', $todo);
        return view('todos.show', compact('todo'));
    }

    public function edit(Todo $todo)
    {
        $this->authorize('update', $todo);
        $lists = Auth::user()->todoLists()->get();
        return view('todos.edit', compact('todo', 'lists'));
    }

    public function update(Request $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'list_id' => 'nullable|exists:todo_lists,id',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        $todo->update($request->only(['title', 'description', 'list_id', 'priority', 'status', 'due_date']));

        return redirect()->route('tasks.index')->with('success', 'Todo updated successfully.');
    }

    public function destroy(Todo $todo)
    {
        $this->authorize('delete', $todo);
        $todo->delete();
        return redirect()->route('tasks.index')->with('success', 'Todo deleted successfully.');
    }

    public function toggleStatus(Todo $todo)
    {
        $this->authorize('update', $todo);
        $todo->update([
            'status' => $todo->status === 'completed' ? 'pending' : 'completed',
            'completed_at' => $todo->status === 'completed' ? now() : null,
        ]);
        return back()->with('success', 'Todo status updated.');
    }

    // New methods for sidebar pages
    public function kanban()
    {
        $todos = Auth::user()->todos()->orderBy('sort_order')->get();
        return view('todos.kanban', compact('todos'));
    }

    public function calendar()
    {
        $todos = Auth::user()->todos()->whereNotNull('due_date')->get();
        return view('todos.calendar', compact('todos'));
    }

    public function archive()
    {
        $todos = Auth::user()->todos()->onlyTrashed()->get();
        return view('todos.archive', compact('todos'));
    }

    public function updateStatus(Request $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $todo->update([
            'status' => $request->status,
            'completed_at' => $request->status === 'completed' ? now() : null,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Status updated']);
        }

        return back()->with('success', 'Status updated successfully.');
    }

    public function restore($id)
    {
        $todo = Todo::onlyTrashed()->findOrFail($id);
        $this->authorize('update', $todo);
        
        $todo->restore();
        
        return redirect()->route('archive')->with('success', 'Todo restored successfully.');
    }
}

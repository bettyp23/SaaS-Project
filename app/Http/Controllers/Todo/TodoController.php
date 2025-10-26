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
        $query = Auth::user()->todos()->with('list');

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

        return redirect()->route('todos.index')->with('success', 'Todo created successfully.');
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

        return redirect()->route('todos.index')->with('success', 'Todo updated successfully.');
    }

    public function destroy(Todo $todo)
    {
        $this->authorize('delete', $todo);
        $todo->delete();
        return redirect()->route('todos.index')->with('success', 'Todo deleted successfully.');
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
}

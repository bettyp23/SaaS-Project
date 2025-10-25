<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use App\Models\TodoList;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TodoController extends Controller
{
    /**
     * Display a listing of the user's todos.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = $user->todos()->with(['list', 'tags', 'attachments', 'comments.user']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by list
        if ($request->has('list_id')) {
            $query->where('list_id', $request->list_id);
        }

        // Filter by tags
        if ($request->has('tags')) {
            $tagIds = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }

        // Filter by due date
        if ($request->has('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }

        // Search in title and description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort by
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['created_at', 'updated_at', 'due_date', 'priority', 'title'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $todos = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $todos,
            'meta' => [
                'total' => $todos->total(),
                'per_page' => $todos->perPage(),
                'current_page' => $todos->currentPage(),
                'last_page' => $todos->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created todo.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Check if user can create more todos
        if (!$user->canCreateTodo()) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum number of todos for your plan.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'due_date' => 'nullable|date|after:now',
            'list_id' => 'nullable|exists:todo_lists,id',
            'parent_id' => 'nullable|exists:todos,id',
            'estimated_time' => 'nullable|integer|min:1',
            'is_recurring' => 'boolean',
            'recurring_pattern' => 'nullable|string|in:daily,weekly,monthly,custom',
            'recurring_interval' => 'nullable|integer|min:1',
            'recurring_end_date' => 'nullable|date|after:now',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        // Verify list ownership
        if ($validated['list_id'] ?? null) {
            $list = TodoList::where('id', $validated['list_id'])
                           ->where('user_id', $user->id)
                           ->first();
            
            if (!$list) {
                return response()->json([
                    'success' => false,
                    'message' => 'List not found or access denied.',
                ], 404);
            }
        }

        // Verify parent todo ownership
        if ($validated['parent_id'] ?? null) {
            $parent = Todo::where('id', $validated['parent_id'])
                         ->where('user_id', $user->id)
                         ->first();
            
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent todo not found or access denied.',
                ], 404);
            }
        }

        DB::beginTransaction();
        
        try {
            $todo = $user->todos()->create($validated);

            // Attach tags
            if (!empty($validated['tags'])) {
                $todo->tags()->attach($validated['tags']);
            }

            // Load relationships
            $todo->load(['list', 'tags', 'parent', 'subtasks']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Todo created successfully.',
                'data' => $todo,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create todo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified todo.
     */
    public function show(Todo $todo): JsonResponse
    {
        $user = Auth::user();

        // Check ownership
        if ($todo->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Todo not found or access denied.',
            ], 404);
        }

        $todo->load(['list', 'tags', 'attachments', 'comments.user', 'parent', 'subtasks']);

        return response()->json([
            'success' => true,
            'data' => $todo,
        ]);
    }

    /**
     * Update the specified todo.
     */
    public function update(Request $request, Todo $todo): JsonResponse
    {
        $user = Auth::user();

        // Check ownership
        if ($todo->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Todo not found or access denied.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'due_date' => 'nullable|date',
            'list_id' => 'nullable|exists:todo_lists,id',
            'estimated_time' => 'nullable|integer|min:1',
            'actual_time' => 'nullable|integer|min:0',
            'is_recurring' => 'boolean',
            'recurring_pattern' => 'nullable|string|in:daily,weekly,monthly,custom',
            'recurring_interval' => 'nullable|integer|min:1',
            'recurring_end_date' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        // Handle status change
        if (isset($validated['status'])) {
            if ($validated['status'] === 'completed' && $todo->status !== 'completed') {
                $validated['completed_at'] = now();
            } elseif ($validated['status'] !== 'completed' && $todo->status === 'completed') {
                $validated['completed_at'] = null;
            }
        }

        // Verify list ownership
        if ($validated['list_id'] ?? null) {
            $list = TodoList::where('id', $validated['list_id'])
                           ->where('user_id', $user->id)
                           ->first();
            
            if (!$list) {
                return response()->json([
                    'success' => false,
                    'message' => 'List not found or access denied.',
                ], 404);
            }
        }

        DB::beginTransaction();
        
        try {
            $todo->update($validated);

            // Update tags
            if (array_key_exists('tags', $validated)) {
                $todo->tags()->sync($validated['tags'] ?? []);
            }

            // Load relationships
            $todo->load(['list', 'tags', 'attachments', 'comments.user', 'parent', 'subtasks']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Todo updated successfully.',
                'data' => $todo,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update todo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified todo.
     */
    public function destroy(Todo $todo): JsonResponse
    {
        $user = Auth::user();

        // Check ownership
        if ($todo->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Todo not found or access denied.',
            ], 404);
        }

        try {
            $todo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Todo deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete todo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark todo as completed.
     */
    public function complete(Todo $todo): JsonResponse
    {
        $user = Auth::user();

        // Check ownership
        if ($todo->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Todo not found or access denied.',
            ], 404);
        }

        try {
            $todo->markAsCompleted();
            $todo->load(['list', 'tags']);

            return response()->json([
                'success' => true,
                'message' => 'Todo marked as completed.',
                'data' => $todo,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete todo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark todo as pending.
     */
    public function pending(Todo $todo): JsonResponse
    {
        $user = Auth::user();

        // Check ownership
        if ($todo->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Todo not found or access denied.',
            ], 404);
        }

        try {
            $todo->markAsPending();
            $todo->load(['list', 'tags']);

            return response()->json([
                'success' => true,
                'message' => 'Todo marked as pending.',
                'data' => $todo,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark todo as pending.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk operations on todos.
     */
    public function bulk(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'todo_ids' => 'required|array|min:1',
            'todo_ids.*' => 'exists:todos,id',
            'action' => 'required|string|in:complete,pending,delete,move,add_tags,remove_tags',
            'list_id' => 'nullable|exists:todo_lists,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $todos = $user->todos()->whereIn('id', $validated['todo_ids']);

        DB::beginTransaction();
        
        try {
            switch ($validated['action']) {
                case 'complete':
                    $todos->update(['status' => 'completed', 'completed_at' => now()]);
                    break;
                    
                case 'pending':
                    $todos->update(['status' => 'pending', 'completed_at' => null]);
                    break;
                    
                case 'delete':
                    $todos->delete();
                    break;
                    
                case 'move':
                    if ($validated['list_id']) {
                        $todos->update(['list_id' => $validated['list_id']]);
                    }
                    break;
                    
                case 'add_tags':
                    if ($validated['tags']) {
                        foreach ($todos->get() as $todo) {
                            $todo->tags()->syncWithoutDetaching($validated['tags']);
                        }
                    }
                    break;
                    
                case 'remove_tags':
                    if ($validated['tags']) {
                        foreach ($todos->get() as $todo) {
                            $todo->tags()->detach($validated['tags']);
                        }
                    }
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk operation completed successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk operation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get todo statistics.
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();
        
        $stats = [
            'total' => $user->todos()->count(),
            'completed' => $user->todos()->completed()->count(),
            'pending' => $user->todos()->pending()->count(),
            'in_progress' => $user->todos()->inProgress()->count(),
            'overdue' => $user->todos()->overdue()->count(),
            'due_today' => $user->todos()->dueToday()->count(),
            'due_this_week' => $user->todos()->dueThisWeek()->count(),
            'high_priority' => $user->todos()->highPriority()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TodoList;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TodoListController extends Controller
{
    /**
     * Display a listing of the user's todo lists.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = $user->todoLists()->with(['todos', 'team']);

        // Filter by team
        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        // Filter by visibility
        if ($request->has('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        // Search in name and description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort by
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['created_at', 'updated_at', 'name', 'sort_order'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $lists = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $lists,
            'meta' => [
                'total' => $lists->total(),
                'per_page' => $lists->perPage(),
                'current_page' => $lists->currentPage(),
                'last_page' => $lists->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created todo list.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'team_id' => 'nullable|exists:teams,id',
            'is_public' => 'boolean',
        ]);

        // Verify team membership if team_id is provided
        if ($validated['team_id'] ?? null) {
            $team = Team::find($validated['team_id']);
            if (!$team || !$team->hasMember($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team not found or access denied.',
                ], 404);
            }
        }

        DB::beginTransaction();
        
        try {
            $list = $user->todoLists()->create($validated);
            $list->load(['todos', 'team']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Todo list created successfully.',
                'data' => $list,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create todo list.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified todo list.
     */
    public function show(TodoList $list): JsonResponse
    {
        $user = Auth::user();

        // Check ownership or team membership
        if ($list->user_id !== $user->id && 
            (!$list->team_id || !$list->team->hasMember($user))) {
            return response()->json([
                'success' => false,
                'message' => 'Todo list not found or access denied.',
            ], 404);
        }

        $list->load(['todos.tags', 'team', 'user']);

        return response()->json([
            'success' => true,
            'data' => $list,
        ]);
    }

    /**
     * Update the specified todo list.
     */
    public function update(Request $request, TodoList $list): JsonResponse
    {
        $user = Auth::user();

        // Check ownership or team admin access
        if ($list->user_id !== $user->id && 
            (!$list->team_id || !$list->team->isAdmin($user))) {
            return response()->json([
                'success' => false,
                'message' => 'Todo list not found or access denied.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_public' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Verify team membership if updating team list
        if ($list->team_id && !$list->team->hasMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Team access denied.',
            ], 403);
        }

        try {
            $list->update($validated);
            $list->load(['todos', 'team']);

            return response()->json([
                'success' => true,
                'message' => 'Todo list updated successfully.',
                'data' => $list,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update todo list.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified todo list.
     */
    public function destroy(TodoList $list): JsonResponse
    {
        $user = Auth::user();

        // Check ownership or team admin access
        if ($list->user_id !== $user->id && 
            (!$list->team_id || !$list->team->isAdmin($user))) {
            return response()->json([
                'success' => false,
                'message' => 'Todo list not found or access denied.',
            ], 404);
        }

        try {
            $list->delete();

            return response()->json([
                'success' => true,
                'message' => 'Todo list deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete todo list.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get todos for a specific list.
     */
    public function todos(Request $request, TodoList $list): JsonResponse
    {
        $user = Auth::user();

        // Check ownership or team membership
        if ($list->user_id !== $user->id && 
            (!$list->team_id || !$list->team->hasMember($user))) {
            return response()->json([
                'success' => false,
                'message' => 'Todo list not found or access denied.',
            ], 404);
        }

        $query = $list->todos()->with(['tags', 'attachments', 'comments.user']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
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
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSortFields = ['created_at', 'updated_at', 'due_date', 'priority', 'title', 'sort_order'];
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
     * Get list statistics.
     */
    public function statistics(TodoList $list): JsonResponse
    {
        $user = Auth::user();

        // Check ownership or team membership
        if ($list->user_id !== $user->id && 
            (!$list->team_id || !$list->team->hasMember($user))) {
            return response()->json([
                'success' => false,
                'message' => 'Todo list not found or access denied.',
            ], 404);
        }

        $stats = $list->statistics;

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Reorder todos in the list.
     */
    public function reorder(Request $request, TodoList $list): JsonResponse
    {
        $user = Auth::user();

        // Check ownership or team membership
        if ($list->user_id !== $user->id && 
            (!$list->team_id || !$list->team->hasMember($user))) {
            return response()->json([
                'success' => false,
                'message' => 'Todo list not found or access denied.',
            ], 404);
        }

        $validated = $request->validate([
            'todo_orders' => 'required|array',
            'todo_orders.*.id' => 'required|exists:todos,id',
            'todo_orders.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            foreach ($validated['todo_orders'] as $order) {
                $list->todos()
                    ->where('id', $order['id'])
                    ->update(['sort_order' => $order['sort_order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Todos reordered successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder todos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

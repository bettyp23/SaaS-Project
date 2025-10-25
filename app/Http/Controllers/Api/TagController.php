<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    /**
     * Display a listing of the user's tags.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = $user->tags()->withCount('todos');

        // Search in name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by color
        if ($request->has('color')) {
            $query->where('color', $request->color);
        }

        // Sort by
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSortFields = ['name', 'created_at', 'todos_count'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $tags = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tags,
            'meta' => [
                'total' => $tags->total(),
                'per_page' => $tags->perPage(),
                'current_page' => $tags->currentPage(),
                'last_page' => $tags->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created tag.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        // Check if tag already exists for this user
        $existingTag = $user->tags()->where('name', $validated['name'])->first();
        if ($existingTag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag with this name already exists.',
            ], 409);
        }

        try {
            $tag = $user->tags()->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tag created successfully.',
                'data' => $tag,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tag.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified tag.
     */
    public function show(Tag $tag): JsonResponse
    {
        $user = Auth::user();

        // Check ownership
        if ($tag->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found or access denied.',
            ], 404);
        }

        $tag->loadCount('todos');

        return response()->json([
            'success' => true,
            'data' => $tag,
        ]);
    }

    /**
     * Update the specified tag.
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $user = Auth::user();

        // Check ownership
        if ($tag->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found or access denied.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        // Check if name already exists for this user (excluding current tag)
        if (isset($validated['name'])) {
            $existingTag = $user->tags()
                ->where('name', $validated['name'])
                ->where('id', '!=', $tag->id)
                ->first();
            
            if ($existingTag) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tag with this name already exists.',
                ], 409);
            }
        }

        try {
            $tag->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tag updated successfully.',
                'data' => $tag,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tag.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified tag.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $user = Auth::user();

        // Check ownership
        if ($tag->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found or access denied.',
            ], 404);
        }

        try {
            $tag->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tag deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tag.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get todos for a specific tag.
     */
    public function todos(Request $request, Tag $tag): JsonResponse
    {
        $user = Auth::user();

        // Check ownership
        if ($tag->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found or access denied.',
            ], 404);
        }

        $query = $tag->todos()->with(['list', 'tags']);

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
     * Get tag statistics.
     */
    public function statistics(Tag $tag): JsonResponse
    {
        $user = Auth::user();

        // Check ownership
        if ($tag->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found or access denied.',
            ], 404);
        }

        $stats = $tag->statistics;

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

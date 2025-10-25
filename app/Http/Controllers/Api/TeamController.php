<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    /**
     * Display a listing of the user's teams.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = $user->teams()->with(['owner', 'members']);

        // Filter by role
        if ($request->has('role')) {
            $query->wherePivot('role', $request->role);
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
        
        $allowedSortFields = ['created_at', 'updated_at', 'name'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $teams = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $teams,
            'meta' => [
                'total' => $teams->total(),
                'per_page' => $teams->perPage(),
                'current_page' => $teams->currentPage(),
                'last_page' => $teams->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $team = $user->ownedTeams()->create($validated);

            // Add the creator as the owner
            $team->addMember($user, 'owner');

            $team->load(['owner', 'members']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Team created successfully.',
                'data' => $team,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create team.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team): JsonResponse
    {
        $user = Auth::user();

        // Check membership
        if (!$team->hasMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found or access denied.',
            ], 404);
        }

        $team->load(['owner', 'members.user', 'todoLists']);

        return response()->json([
            'success' => true,
            'data' => $team,
        ]);
    }

    /**
     * Update the specified team.
     */
    public function update(Request $request, Team $team): JsonResponse
    {
        $user = Auth::user();

        // Check ownership or admin access
        if (!$team->isOwner($user) && !$team->isAdmin($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found or access denied.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $team->update($validated);
            $team->load(['owner', 'members.user']);

            return response()->json([
                'success' => true,
                'message' => 'Team updated successfully.',
                'data' => $team,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update team.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified team.
     */
    public function destroy(Team $team): JsonResponse
    {
        $user = Auth::user();

        // Check ownership
        if (!$team->isOwner($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Only team owners can delete teams.',
            ], 403);
        }

        try {
            $team->delete();

            return response()->json([
                'success' => true,
                'message' => 'Team deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete team.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add a member to the team.
     */
    public function addMember(Request $request, Team $team): JsonResponse
    {
        $user = Auth::user();

        // Check admin access
        if (!$team->isOwner($user) && !$team->isAdmin($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:admin,member,viewer',
        ]);

        $member = User::find($validated['user_id']);

        // Check if user is already a member
        if ($team->hasMember($member)) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member of this team.',
            ], 409);
        }

        try {
            $team->addMember($member, $validated['role'], $user);

            return response()->json([
                'success' => true,
                'message' => 'Member added successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add member.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a team member's role.
     */
    public function updateMember(Request $request, Team $team, User $member): JsonResponse
    {
        $user = Auth::user();

        // Check admin access
        if (!$team->isOwner($user) && !$team->isAdmin($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        // Check if member exists
        if (!$team->hasMember($member)) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a member of this team.',
            ], 404);
        }

        $validated = $request->validate([
            'role' => 'required|string|in:admin,member,viewer',
        ]);

        // Prevent changing owner's role
        if ($team->isOwner($member)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change owner role.',
            ], 400);
        }

        try {
            $team->updateMemberRole($member, $validated['role']);

            return response()->json([
                'success' => true,
                'message' => 'Member role updated successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update member role.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Team $team, User $member): JsonResponse
    {
        $user = Auth::user();

        // Check admin access or self-removal
        if (!$team->isOwner($user) && !$team->isAdmin($user) && $user->id !== $member->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        // Check if member exists
        if (!$team->hasMember($member)) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a member of this team.',
            ], 404);
        }

        // Prevent removing owner
        if ($team->isOwner($member)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove team owner.',
            ], 400);
        }

        try {
            $team->removeMember($member);

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove member.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get team members.
     */
    public function members(Team $team): JsonResponse
    {
        $user = Auth::user();

        // Check membership
        if (!$team->hasMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found or access denied.',
            ], 404);
        }

        $members = $team->members()->with('user')->get();

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    /**
     * Get team statistics.
     */
    public function statistics(Team $team): JsonResponse
    {
        $user = Auth::user();

        // Check membership
        if (!$team->hasMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found or access denied.',
            ], 404);
        }

        $stats = $team->statistics;

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Invite a user to the team.
     */
    public function invite(Request $request, Team $team): JsonResponse
    {
        $user = Auth::user();

        // Check admin access
        if (!$team->isOwner($user) && !$team->isAdmin($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        $validated = $request->validate([
            'email' => 'required|string|email',
            'role' => 'required|string|in:admin,member,viewer',
        ]);

        // Check if user exists
        $invitedUser = User::where('email', $validated['email'])->first();
        if (!$invitedUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Check if user is already a member
        if ($team->hasMember($invitedUser)) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member of this team.',
            ], 409);
        }

        try {
            // In a real app, you'd send an email invitation here
            // For now, we'll just add them directly
            $team->addMember($invitedUser, $validated['role'], $user);

            return response()->json([
                'success' => true,
                'message' => 'User invited successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to invite user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Join a team (for public teams or with invitation).
     */
    public function join(Request $request, Team $team): JsonResponse
    {
        $user = Auth::user();

        // Check if user is already a member
        if ($team->hasMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this team.',
            ], 409);
        }

        try {
            $team->addMember($user, 'member');

            return response()->json([
                'success' => true,
                'message' => 'Successfully joined the team.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to join team.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

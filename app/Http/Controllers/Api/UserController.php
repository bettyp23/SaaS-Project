<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    /**
     * Get user profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['subscription.plan', 'preferences']);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'timezone' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user->update($request->only(['name', 'email', 'timezone']));
            $user->load(['subscription.plan', 'preferences']);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload user avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            // Delete old avatar if exists
            if ($user->profile_picture) {
                Storage::delete('public/' . $user->profile_picture);
            }

            // Process and store new avatar
            $file = $request->file('avatar');
            $filename = 'avatars/' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Resize image to 200x200
            $image = Image::make($file)->fit(200, 200);
            Storage::put('public/' . $filename, $image->encode());

            $user->update(['profile_picture' => $filename]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully.',
                'data' => [
                    'avatar_url' => $user->avatar_url,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->profile_picture) {
                Storage::delete('public/' . $user->profile_picture);
                $user->update(['profile_picture' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Avatar deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete avatar.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user preferences.
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        $preferences = $user->preferences()->pluck('value', 'key');

        return response()->json([
            'success' => true,
            'data' => $preferences,
        ]);
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'theme' => 'sometimes|string|in:light,dark,auto',
            'notifications' => 'sometimes|boolean',
            'email_notifications' => 'sometimes|boolean',
            'due_date_reminders' => 'sometimes|boolean',
            'weekly_summary' => 'sometimes|boolean',
            'language' => 'sometimes|string|in:en,es,fr,de',
            'date_format' => 'sometimes|string|in:Y-m-d,m/d/Y,d/m/Y',
            'time_format' => 'sometimes|string|in:12,24',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            foreach ($request->all() as $key => $value) {
                $user->setPreference($key, $value);
            }

            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update preferences.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'todos' => [
                'total' => $user->todos()->count(),
                'completed' => $user->todos()->completed()->count(),
                'pending' => $user->todos()->pending()->count(),
                'overdue' => $user->todos()->overdue()->count(),
            ],
            'lists' => [
                'total' => $user->todoLists()->count(),
                'completed' => $user->todoLists()->where('is_completed', true)->count(),
            ],
            'teams' => [
                'owned' => $user->ownedTeams()->count(),
                'member' => $user->teams()->count(),
            ],
            'subscription' => [
                'plan' => $user->getSubscriptionPlan()?->name ?? 'Free',
                'status' => $user->subscription?->status ?? 'active',
                'expires_at' => $user->subscription?->current_period_end,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Delete user account.
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'confirm' => 'required|boolean|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect password.',
                ], 400);
            }

            // Delete user data (this will cascade delete related records)
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export user data.
     */
    public function exportData(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $data = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'timezone' => $user->timezone,
                    'created_at' => $user->created_at,
                ],
                'todos' => $user->todos()->with(['list', 'tags'])->get(),
                'lists' => $user->todoLists()->with(['todos'])->get(),
                'teams' => $user->teams()->with(['owner'])->get(),
                'preferences' => $user->preferences()->get(),
                'exported_at' => now(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

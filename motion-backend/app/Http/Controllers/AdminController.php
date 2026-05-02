<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Event;
use App\Models\Group;

class AdminController extends Controller
{
    // Get admin dashboard stats
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_clients' => Client::count(),
            'total_events' => Event::count(),
            'total_groups' => Group::count(),
            'total_admins' => Admin::count(),
            'recent_users' => User::latest()->take(5)->get(['user_id', 'name', 'surname', 'email', 'created_at'])
        ];

        return response()->json([
            'stats' => $stats,
            'message' => 'Dashboard data retrieved successfully'
        ]);
    }

    // Get admin profile
    public function getProfile(Request $request)
    {
        $user = $request->user();
        $admin = $user->admin;

        if (!$admin) {
            return response()->json(['message' => 'Admin profile not found'], 404);
        }

        return response()->json([
            'admin' => [
                'user' => $user,
                'position_type' => $admin->position_type,
                'bio_description' => $admin->bio_description,
                'photo_url' => $admin->photo_url,
            ],
            'message' => 'Admin profile retrieved successfully'
        ]);
    }
    

    // Get all groups
    public function getGroups()
    {
        $groups = Group::all();
        
        return response()->json([
            'groups' => $groups,
            'message' => 'Groups retrieved successfully'
        ]);
    }

    // Create new group
    public function createGroup(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'level' => 'required|in:beginner,intermediate,advanced',
            'member_count' => 'nullable|integer|min:0|max:50'
        ]);

        $group = Group::create([
            'title' => $request->title,
            'level' => $request->level,
            'member_count' => $request->member_count ?? 0
        ]);

        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group
        ], 201);
    }

    // Get all instructors for dropdown
    public function getInstructors()
    {
        $instructors = Admin::with('user')
            ->where('position_type', 'instructor')
            ->get()
            ->map(function($admin) {
                return [
                    'admin_id' => $admin->admin_id,
                    'name' => $admin->user->name . ' ' . $admin->user->surname,
                    'email' => $admin->user->email,
                    'position_type' => $admin->position_type,
                    'bio_description' => $admin->bio_description
                ];
            });

        return response()->json([
            'instructors' => $instructors,
            'message' => 'Instructors retrieved successfully'
        ]);
    }

    public function deleteGroup($id)
    {
        try {
            $group = Group::findOrFail($id);
            $group->delete();

            return response()->json([
                'message' => 'Group deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteInstructor($id)
{
    try {
        $instructor = Admin::findOrFail($id);
        $instructor->delete();

        return response()->json([
            'message' => 'Instructor removed successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to remove instructor',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // Create new instructor
    public function createInstructor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:30|regex:/^[\p{L}\s\-\']+$/u',
            'surname' => 'required|string|max:30|regex:/^[\p{L}\s\-\']+$/u',
            'email' => 'required|string|max:100|unique:users|regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/|confirmed',
            'phone_number' => 'required|string|max:20', 
            'birth_date' => 'required|date|before:today',
            'bio_description' => 'nullable|string|max:1000',
            'photo_url' => 'nullable|url'
        ]);

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'birth_date' => $request->birth_date
        ]);

        $instructor = Admin::create([
            'user_id' => $user->user_id,
            'position_type' => 'instructor',
            'bio_description' => $request->bio_description,
            'photo_url' => $request->photo_url
        ]);

        return response()->json([
            'message' => 'Instructor created successfully',
            'instructor' => $instructor->load('user')
        ], 201);
        }
}

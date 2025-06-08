<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Event;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'message' => 'user profile retrieved successfully'
        ]);
    }

    public function getUserGroups(Request $request)
    {
        $user = $request->user();
        
        // get user's groups with contracts info
        $groups = $user->groups()
            ->with('style')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->group_id,
                    'name' => $group->title,
                    'type' => $group->style->title ?? 'unknown',
                    'level' => $group->level,
                    'status' => 'active',
                    'joined_date' => $group->pivot->registration_date,
                    'end_date' => $group->pivot->end_date,
                    'monthly_fee' => $group->pivot->monthly_fee,
                    'role' => 'member'
                ];
            });
        
        return response()->json([
            'groups' => $groups,
            'message' => 'user groups retrieved successfully'
        ]);
    }

    public function getUserEvents(Request $request)
    {
        $user = $request->user();
        
        $events = $user->events()
            ->with('style')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->event_id,
                    'name' => $event->style->title ?? 'dance event',
                    'description' => $event->style->description ?? '',
                    'date' => $event->start_date,
                    'time' => $event->start_time,
                    'location' => ucfirst(str_replace('_', ' ', $event->hall)),
                    'level' => $event->level,
                    'duration' => $event->duration_minutes . ' minutes',
                    'attendance_status' => 'confirmed',
                    'registration_date' => $event->pivot->registration_date
                ];
            });
        
        return response()->json([
            'events' => $events,
            'message' => 'user events retrieved successfully'
        ]);
    }

    public function updateEventStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:confirmed,cancelled,pending'
        ]);

        $user = $request->user();
        
        $registration = $user->registrations()
            ->where('event_id', $id)
            ->first();

        if (!$registration) {
            return response()->json([
                'message' => 'event registration not found'
            ], 404);
        }

        if ($request->status === 'cancelled') {
            $registration->delete();
            return response()->json([
                'message' => 'event registration cancelled successfully'
            ]);
        }

        return response()->json([
            'message' => 'event status updated successfully'
        ]);
    }

    public function leaveGroup(Request $request, $groupId)
    {
        $user = $request->user();
        
        $contract = $user->contracts()
            ->where('group_id', $groupId)
            ->first();

        if (!$contract) {
            return response()->json([
                'message' => 'group membership not found'
            ], 404);
        }

        $contract->update(['end_date' => now()]);

        return response()->json([
            'message' => 'successfully left the group'
        ]);
    }
}

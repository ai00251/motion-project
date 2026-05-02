<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Event;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Registration;
use App\Models\User;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
        $role = $user->getRole(); 
        
        return response()->json([
            'user' => $user,
            'role' => $role,
            'message' => 'user profile retrieved successfully'
        ]);
    }


    // for regular users - register for events
    public function registerForEvent(Request $request, $eventId)
    {
        $user = $request->user();
        
        $event = Event::findOrFail($eventId);
        
        // check capacity
        if ($event->registrations()->count() >= $event->capacity) {
            return response()->json(['message' => 'event is full'], 400);
        }
        
        // if user is not a client yet, create a client record
        $client = $user->client;
        if (!$client) {
            $client = Client::create([
                'user_id' => $user->user_id,
            ]);
        }
        
        // check if already registered using client_id
        if (Registration::where('event_id', $eventId)->where('client_id', $client->client_id)->exists()) {
            return response()->json(['message' => 'already registered for this event'], 400);
        }
        
        // create registration using client_id
        Registration::create([
            'event_id' => $eventId,
            'client_id' => $client->client_id,
            'registration_date' => now()
        ]);
        
        return response()->json(['message' => 'successfully registered for event']);
    }

    // for users wanting to become clients
    public function joinGroup(Request $request, $groupId)
    {
        $user = $request->user();
        
        if ($user->isClient()) {
            return response()->json(['message' => 'you are already a client'], 400);
        }
        
        $group = Group::findOrFail($groupId);
        
        // create client record if doesn't exist
        $client = $user->client;
        if (!$client) {
            $client = Client::create([
                'user_id' => $user->user_id,
            ]);
        }
        
        // create contract (makes them a client)
        Contract::create([
            'client_id' => $client->client_id,
            'group_id' => $groupId,
            'registration_date' => now(),
            'end_date' => now()->addMonths(3), // 3 month contract
            'monthly_fee' => 60.00 // or get from group settings
        ]);
        
        return response()->json([
            'message' => 'welcome to the group! you are now a client',
            'user' => $user->fresh(),
            'role' => $user->getRole()
        ]);
    }

    public function getUserGroups(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isClient()) {
            return response()->json([
                'groups' => [],
                'message' => 'you need to be a client to have groups'
            ]);
        }
        
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
        
        // get user's client record first
        $client = $user->client;
        if (!$client) {
            return response()->json([
                'events' => [],
                'message' => 'no events found'
            ]);
        }
        
        // get user's registered events using client_id
        $registrations = Registration::where('client_id', $client->client_id)
            ->with('event.style')
            ->get();
        
        $events = $registrations->map(function ($registration) {
            $event = $registration->event;
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
                'registration_date' => $registration->registration_date
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
        $client = $user->client;
        
        if (!$client) {
            return response()->json([
                'message' => 'client record not found'
            ], 404);
        }
        
        $registration = Registration::where('client_id', $client->client_id)
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
        
        if (!$user->isClient()) {
            return response()->json(['message' => 'only clients can leave groups'], 400);
        }
        
        $client = $user->client;
        $contract = Contract::where('client_id', $client->client_id)
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

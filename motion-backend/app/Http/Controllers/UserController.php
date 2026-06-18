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
    // atgriež lietotāja profilu kopā ar noteikto lomu
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

    // piesaka lietotāju uz pasākumu un pārbauda vietu pieejamību
    public function registerForEvent(Request $request, $eventId)
    {
        $user = $request->user();
        $event = Event::findOrFail($eventId);

        // pirms reģistrācijas pārbauda, vai pasākums vēl nav pilns
        if ($event->registrations()->count() >= $event->capacity) {
            return response()->json(['message' => 'event is full'], 400);
        }

        // ja lietotājam vēl nav klienta ieraksta, to izveido automātiski
        $client = $user->client;
        if (!$client) {
            $client = Client::create([
                'user_id' => $user->user_id,
            ]);
        }

        // nepieļauj dubultu reģistrāciju uz to pašu pasākumu
        if (Registration::where('event_id', $eventId)->where('client_id', $client->client_id)->exists()) {
            return response()->json(['message' => 'already registered for this event'], 400);
        }

        // izveido jaunu reģistrāciju ar pašreizējo datumu
        Registration::create([
            'event_id' => $eventId,
            'client_id' => $client->client_id,
            'registration_date' => now()
        ]);

        return response()->json(['message' => 'successfully registered for event']);
    }

    // pievieno lietotāju grupai, izveidojot līgumu un klienta ierakstu
    public function joinGroup(Request $request, $groupId)
    {
        $user = $request->user();

        if ($user->isClient()) {
            return response()->json(['message' => 'you are already a client'], 400);
        }

        $group = Group::findOrFail($groupId);

        // ja klienta profils vēl neeksistē, tas tiek izveidots šeit
        $client = $user->client;
        if (!$client) {
            $client = Client::create([
                'user_id' => $user->user_id,
            ]);
        }

        // izveido līgumu ar noklusēto termiņu un maksu
        Contract::create([
            'client_id' => $client->client_id,
            'group_id' => $groupId,
            'registration_date' => now(),
            'end_date' => now()->addMonths(3),
            'monthly_fee' => 60.00
        ]);

        return response()->json([
            'message' => 'welcome to the group! you are now a client',
            'user' => $user->fresh(),
            'role' => $user->getRole()
        ]);
    }

    // atgriež visas grupas, kurās lietotājs ir reģistrēts
    public function getUserGroups(Request $request)
    {
        $user = $request->user();

        $client = $user->client;
        if (!$client) {
            return response()->json([
                'groups' => [],
                'message' => 'you need to be a client to have groups'
            ]);
        }

        // ielādē līgumus kopā ar grupas stila informāciju
        $contracts = Contract::where('client_id', $client->client_id)
            ->with(['group.style'])
            ->get();

        // pārveido līgumus par vienkāršu grupu sarakstu frontenda vajadzībām
        $groups = $contracts->map(function ($contract) {
            $group = $contract->group;

            return [
                'id' => $group->group_id,
                'name' => $group->title,
                'type' => $group->style->title ?? 'unknown',
                'level' => $group->level,
                'status' => $contract->end_date && $contract->end_date < now() ? 'inactive' : 'active',
                'joined_date' => $contract->registration_date,
                'end_date' => $contract->end_date,
                'monthly_fee' => $contract->monthly_fee,
                'role' => 'member'
            ];
        });

        return response()->json([
            'groups' => $groups,
            'message' => 'user groups retrieved successfully'
        ]);
    }

    // atgriež visas lietotāja reģistrētās nodarbības
    public function getUserEvents(Request $request)
    {
        $user = $request->user();
        $client = $user->client;

        if (!$client) {
            return response()->json([
                'events' => [],
                'message' => 'no events found'
            ]);
        }

        // ielādē reģistrācijas kopā ar saistīto pasākumu un stilu
        $registrations = Registration::where('client_id', $client->client_id)
            ->with('event.style')
            ->get();

        // sagatavo pasākumu datus frontenda sarakstam
        $events = $registrations->map(function ($registration) {
            $event = $registration->event;

            return [
                'id' => $event->event_id,
                'name' => $event->style->title ?? 'dance event',
                'description' => $event->style->description ?? '',
                'level' => $event->level,
                'duration_minutes' => $event->duration_minutes,
                'hall' => $event->hall,
                'start_date' => $event->start_date,
                'start_time' => $event->start_time,
                'capacity' => $event->capacity,
                'registered_count' => $event->registrations()->count(),
                'attendance_status' => 'confirmed',
                'registration_date' => $registration->registration_date
            ];
        });

        return response()->json([
            'events' => $events,
            'message' => 'user events retrieved successfully'
        ]);
    }

    // maina lietotāja reģistrācijas statusu uz izvēlēto vērtību
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

        // sameklē konkrēto reģistrāciju pēc klienta un pasākuma
        $registration = Registration::where('client_id', $client->client_id)
            ->where('event_id', $id)
            ->first();

        if (!$registration) {
            return response()->json([
                'message' => 'event registration not found'
            ], 404);
        }

        // ja statuss ir cancelled, reģistrācija tiek pilnībā dzēsta
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

    // ļauj lietotājam pamest grupu un aizver līgumu
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

        // noslēdz līgumu, iestatot beigu datumu uz šodienu
        $contract->update(['end_date' => now()]);

        return response()->json([
            'message' => 'successfully left the group'
        ]);
    }
}
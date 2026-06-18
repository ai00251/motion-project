<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // atgriež visus pasākumus kopā ar saistīto stilu informāciju
    public function index()
    {
        // ielādē stilu datus un pārveido tos frontenda vajadzībām
        $events = Event::with('style')->get()->map(function ($event) {
            return [
                'id' => $event->event_id,
                'name' => $event->style->title ?? 'dance class',
                'description' => $event->style->description ?? '',
                'level' => $event->level,
                'duration_minutes' => $event->duration_minutes,
                'hall' => $event->hall,
                'start_date' => $event->start_date,
                'start_time' => $event->start_time,
                'capacity' => $event->capacity,
                'image' => $event->style->image ?? null,
                'registered_count' => $event->registrations()->count()
            ];
        });

        return response()->json([
            'events' => $events,
            'message' => 'events retrieved successfully'
        ]);
    }

    // atgriež vienu pasākumu pēc id ar detalizētu informāciju
    public function show($id)
    {
        $event = Event::with('style')->findOrFail($id);

        // sagatavo detalizētu atbildi, lai frontends varētu rādīt pilnu informāciju
        return response()->json([
            'event' => [
                'id' => $event->event_id,
                'title' => $event->style->title ?? 'dance class',
                'description' => $event->style->description ?? '',
                'level' => $event->level,
                'duration' => $event->duration_minutes . ' minutes',
                'hall' => ucfirst(str_replace('_', ' ', $event->hall)),
                'start_date' => $event->start_date,
                'start_time' => $event->start_time,
                'capacity' => $event->capacity,
                'registered_count' => $event->registrations()->count()
            ],
            'message' => 'event retrieved successfully'
        ]);
    }

    // izveido jaunu pasākumu pēc validācijas
    public function store(Request $request)
    {
        $request->validate([
            'style_id' => 'required|exists:styles,style_id',
            'duration_minutes' => 'required|integer|min:15|max:180',
            'hall' => 'required|in:hall_1,hall_2,hall_3,outdoor',
            'level' => 'required|in:beginner,intermediate,advanced',
            'start_time' => 'required|date_format:H:i',
            'start_date' => 'required|date|after_or_equal:today',
            'capacity' => 'required|integer|min:1|max:50',
        ]);

        // saglabā jauno ierakstu tieši no saņemtajiem datiem
        $event = Event::create($request->all());

        return response()->json([
            'event' => $event,
            'message' => 'event created successfully'
        ], 201);
    }
}
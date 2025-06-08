<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('style')->get();
        
        return response()->json([
            'events' => $events,
            'message' => 'Events retrieved successfully'
        ]);
    }

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

        $event = Event::create($request->all());

        return response()->json([
            'event' => $event,
            'message' => 'Event created successfully'
        ], 201);
    }
}

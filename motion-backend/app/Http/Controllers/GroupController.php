<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
{
    $groups = Group::with('style')->get()->map(function ($group) {
        return [
            'id' => $group->group_id,
            'name' => $group->title,
            'title' => $group->title,
            'level' => $group->level,
            'member_count' => $group->member_count, // Use existing field from database
            'current_participants' => $group->member_count, // Use existing field
            'max_participants' => 20, // Just for display, not used for validation
            'style' => $group->style->title ?? 'unknown',
            'style_name' => $group->style->title ?? 'unknown',
            'description' => $group->style->description ?? '',
            'schedule' => $this->getScheduleByLevel($group->level),
            'time' => $this->getTimeByLevel($group->level),
            'duration' => $this->getDurationByLevel($group->level),
            'price' => $this->getPriceByLevel($group->level)
        ];
    });
    
    return response()->json([
        'groups' => $groups,
        'message' => 'groups retrieved successfully'
    ]);
}


    public function show($id)
    {
        $group = Group::with('style')->findOrFail($id);
        
        return response()->json([
            'group' => [
                'id' => $group->group_id,
                'name' => $group->title,
                'title' => $group->title,
                'level' => $group->level,
                'member_count' => $group->member_count,
                'current_participants' => $group->member_count,
                'max_participants' => 20,
                'style' => $group->style->title ?? 'unknown',
                'description' => $group->style->description ?? '',
                'schedule' => $this->getScheduleByLevel($group->level),
                'time' => $this->getTimeByLevel($group->level),
                'duration' => $this->getDurationByLevel($group->level),
                'price' => $this->getPriceByLevel($group->level)
            ],
            'message' => 'group retrieved successfully'
        ]);
    }

    private function getScheduleByLevel($level)
    {
        $schedules = [
            'beginner' => 'mon & wed',
            'intermediate' => 'tue & thu', 
            'advanced' => 'fri & sat'
        ];
        return $schedules[$level] ?? 'flexible';
    }

    private function getTimeByLevel($level)
    {
        $times = [
            'beginner' => '6:30 pm',
            'intermediate' => '7:30 pm',
            'advanced' => '8:00 pm'
        ];
        return $times[$level] ?? '7:00 pm';
    }

    private function getDurationByLevel($level)
    {
        $durations = [
            'beginner' => 10,
            'intermediate' => 8,
            'advanced' => 6
        ];
        return $durations[$level] ?? 8;
    }

    private function getPriceByLevel($level)
    {
        $prices = [
            'beginner' => 120,
            'intermediate' => 150,
            'advanced' => 180
        ];
        return $prices[$level] ?? 140;
    }
}

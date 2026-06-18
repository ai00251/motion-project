<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // atgriež visu grupu sarakstu ar papildu informāciju frontenda skatam
    public function index()
    {
        // katrai grupai pievieno stilam piesaistītos datus un aprēķinātos laukus
        $groups = Group::with('style')->get()->map(function ($group) {
            return [
                'id' => $group->group_id,
                'name' => $group->title,
                'title' => $group->title,
                'level' => $group->level,
                'member_count' => $group->member_count,
                'current_participants' => $group->member_count,
                'max_participants' => 20,
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

    // atgriež vienu grupu ar tādu pašu datu struktūru kā sarakstā
    public function show($id)
    {
        $group = Group::with('style')->findOrFail($id);

        // sagatavo detalizētu atbildi ar grafiku, ilgumu un cenu
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

    // nosaka nodarbību dienas pēc grupas līmeņa
    private function getScheduleByLevel($level)
    {
        $schedules = [
            'beginner' => 'mon & wed',
            'intermediate' => 'tue & thu',
            'advanced' => 'fri & sat'
        ];

        return $schedules[$level] ?? 'flexible';
    }

    // nosaka nodarbību sākuma laiku atbilstoši līmenim
    private function getTimeByLevel($level)
    {
        $times = [
            'beginner' => '6:30 pm',
            'intermediate' => '7:30 pm',
            'advanced' => '8:00 pm'
        ];

        return $times[$level] ?? '7:00 pm';
    }

    // atgriež nodarbības ilgumu minūtēs pēc līmeņa
    private function getDurationByLevel($level)
    {
        $durations = [
            'beginner' => 10,
            'intermediate' => 8,
            'advanced' => 6
        ];

        return $durations[$level] ?? 8;
    }

    // aprēķina cenu pēc grupas līmeņa
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
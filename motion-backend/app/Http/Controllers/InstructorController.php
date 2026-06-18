<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;

class InstructorController extends Controller
{
    // atgriež visus instruktorus ar pamata informāciju frontenda kartītēm
    public function index()
    {
        // ielādē tikai tos adminus, kuriem ir instruktora loma, un sagatavo datus attēlošanai
        $instructors = Admin::with('user')
            ->where('position_type', 'instructor')
            ->get()
            ->map(function ($admin) {
                return [
                    'name' => $admin->user->name . ' ' . $admin->user->surname,
                    'photo' => $admin->photo_url ?: 'https://via.placeholder.com/100',
                    'description' => $admin->bio_description ?: 'experienced dance instructor'
                ];
            });

        return response()->json(['instructors' => $instructors]);
    }
}
<?php
// app/Http/Controllers/StyleController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Style;

class StyleController extends Controller
{
    public function index()
    {
        $styles = Style::select('title', 'description')
            ->get()
            ->map(function($style) {
                return [
                    'title' => $style->title,
                    'description' => $style->description ?: 'energetic, expressive, and always evolving. all levels welcome.'
                ];
            });

        return response()->json(['styles' => $styles]);
    }
}

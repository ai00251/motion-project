<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Style;

class StyleController extends Controller
{
    // atgriež visu stilu sarakstu ar īsu aprakstu frontenda vajadzībām
    public function index()
    {
        // atlasām tikai vajadzīgos laukus, lai atbildē nebūtu lieku datu
        $styles = Style::select('title', 'description')
            ->get()
            ->map(function ($style) {
                return [
                    'title' => $style->title,
                    'description' => $style->description ?: 'energetic, expressive, and always evolving. all levels welcome.'
                ];
            });

        return response()->json(['styles' => $styles]);
    }
}
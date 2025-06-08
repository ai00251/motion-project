<?php

namespace App\Http\Controllers;

use App\Models\Style;
use Illuminate\Http\Request;

class StyleController extends Controller
{
    public function index()
    {
        $styles = Style::all();
        
        return response()->json([
            'styles' => $styles,
            'message' => 'Dance styles retrieved successfully'
        ]);
    }
}

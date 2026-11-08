<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedTrack;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'featured' => FeaturedTrack::count(),
            'active' => FeaturedTrack::active()->count(),
            'users' => User::count(),
        ];

        $recentTracks = FeaturedTrack::ordered()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentTracks'));
    }
}

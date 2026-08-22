<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $user = auth()->user();
        
        // Fetch apps assigned to the user
        $apps = $user->apps;

        $laporan_list = $apps->map(function($app) {
            // Mapping for dynamic data not yet in DB
            $config = [
                'money-management' => [
                    'route' => 'money-management.dashboard',
                    'icon' => 'ki-outline ki-dollar',
                ],
                'travel-planner' => [
                    'route' => 'travel.dashboard',
                    'icon' => 'ki-outline ki-map',
                ],
                // Add more mappings as new apps are added
            ];

            $appConfig = $config[$app->code] ?? [
                'route' => 'dashboard', // Default
                'icon' => 'ki-outline ki-element-11',
            ];

            return [
                'nama' => $app->name,
                'route' => $appConfig['route'],
                'permission' => $app->code, // Still check for app-level access
                'description' => $app->description,
                'icon' => $appConfig['icon'],
                'btn' => 'Buka Aplikasi'
            ];
        });

        return view('pages.dashboard', compact('laporan_list'));
    }
}

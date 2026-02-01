<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $laporan_list = [
            [
                'nama' => 'Money Management System',
                'route' => 'money-management.dashboard',
                'permission' => 'money-management',
                'description' => 'Aplikasi Manajemen Keuangan',
                'icon' => 'ki-outline ki-dollar',
                'btn' => 'Buka Aplikasi'
            ],
        ];

        return view('pages.dashboard', compact('laporan_list'));
    }
}

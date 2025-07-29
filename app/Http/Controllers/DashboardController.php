<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            return view('welcome');
        } catch (\Exception $e) {
            Log::error('Error in DashboardController@index: ' . $e->getMessage());
            return view('welcome')->with('error', 'Terjadi kesalahan saat memuat halaman.');
        }
    }
}

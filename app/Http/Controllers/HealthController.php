<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class HealthController extends Controller
{
    public function check()
    {
        try {
            DB::connection()->getPdo();
            $dbStatus = "CONNECTED";
            $userCount = DB::table('users')->count();
        } catch (Exception $e) {
            $dbStatus = "DISCONNECTED: " . $e->getMessage();
            $userCount = 0;
        }

        return response()->json([
            'system_name' => 'agroOrderGPA',
            'sprint_stage' => 'Sprint 1 - Foundation Ready',
            'database_status' => $dbStatus,
            'active_users_seeded' => $userCount,
            'timestamp' => now()->toIso8601String()
        ]);
    }
}

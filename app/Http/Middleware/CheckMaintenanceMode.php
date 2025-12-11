<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Fetch maintenance mode setting
        // Ideally checking cache first would be better for performance, but for now DB is fine
        $maintenanceMode = Setting::where('key', 'maintenance_mode')->value('value');

        // Check if maintenance mode is enabled (value '1' or true)
        if ($maintenanceMode && ($maintenanceMode === '1' || $maintenanceMode === 'true' || $maintenanceMode === true)) {
            
            // Allow login/admin routes if needed? 
            // For now, let's just check if the user is authenticated and is an admin.
            
            // If user is logged in
            if (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user();
                if ($user->hasRole('admin')) {
                    return $next($request);
                }
            }

            // Exceptions: Allow login endpoint so admins can actually log in
            if ($request->is('api/auth/login')) {
                 return $next($request);
            }

            return response()->json([
                'message' => 'System is in maintenance mode. Please try again later.',
                'maintenance_mode' => true
            ], 503);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            Log::channel('single')->info('RoleMiddleware: User not authenticated');
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $user = Auth::user();
        Log::channel('single')->info('RoleMiddleware: User authenticated', ['user_id' => $user->id, 'user_email' => $user->email, 'user_role' => $user->role]);
        $userRole = $user->role;

        
        // Convert user role to camelCase for comparison
        $userRoleCamelCase = Str::camel($userRole);
        $camelCaseRoles = array_map(fn($role) => Str::camel($role), $roles);
        Log::channel('single')->info('RoleMiddleware: Incoming primary role - '. $userRoleCamelCase .', Required roles - '. implode(',', $camelCaseRoles));
        
        if ($userRoleCamelCase !== 'member' && in_array($userRoleCamelCase, $camelCaseRoles)) {
            Log::channel('single')->info('RoleMiddleware: User primary role matched (non-member)', ['role' => $userRoleCamelCase]);
            return $next($request);
        }

        Log::channel('single')->info('RoleMiddleware: User clubs', ['clubs' => $user->clubs->toArray()]);

        // Check for club-specific roles if the user is a member of a club
        if ($user->clubs->isNotEmpty()) {
            foreach ($user->clubs as $club) {
                $pivotRole = $club->pivot->role;
                Log::channel('single')->info('RoleMiddleware: Club-specific role', ['club_id' => $club->id, 'pivot_role' => $pivotRole]);
                $pivotRoleCamelCase = Str::camel($pivotRole);
                if (in_array($pivotRoleCamelCase, $camelCaseRoles)) {
                    Log::channel('single')->info('RoleMiddleware: Club-specific role matched', ['club_id' => $club->id, 'pivot_role' => $pivotRoleCamelCase]);
                    return $next($request);
                }
            }
        }

        Log::channel('single')->info('RoleMiddleware: User not authorized for any role. Denying access.', ['user_id' => $user->id, 'user_email' => $user->email, 'required_roles' => $camelCaseRoles]);
        return response()->json(['message' => 'Unauthorized.'], 403);
    }
}
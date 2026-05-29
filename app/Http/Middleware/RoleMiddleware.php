<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        // ✅ تحقق أن المستخدم مسجل دخول وله الدور المطلوب
        if (!$request->user() || $request->user()->role !== $role) {
            return response()->json([
                'message' => 'Accès refusé — rôle insuffisant'
            ], 403);
        }

        return $next($request);
    }
}
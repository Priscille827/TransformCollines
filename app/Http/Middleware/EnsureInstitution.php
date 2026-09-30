<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureInstitution
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !in_array($user->type_compte, ['institution', 'admin'])) {
            abort(403, 'Accès réservé aux acteurs institutionnels.');
        }

        return $next($request);
    }
}
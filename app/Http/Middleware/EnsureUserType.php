<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserType
{
    /**
     * Uso na rota: ->middleware('user.type:candidate') ou 'user.type:company'
     */
    public function handle(Request $request, Closure $next, string ...$types)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->type ?? 'candidate', $types, true)) {
            abort(403, 'Esta área é restrita a ' . implode('/', $types) . '.');
        }

        return $next($request);
    }
}

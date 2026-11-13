<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_admin) {
            if ($request->expectsJson()) {
                abort(403, 'Akses admin diperlukan.');
            }

            return redirect()
                ->route('home')
                ->with('error', 'Halaman itu khusus admin SoundKu.');
        }

        return $next($request);
    }
}

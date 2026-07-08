<?php

declare(strict_types=1);

namespace Workbench\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Workbench\App\Models\User;

final class AuthenticateWorkbenchUser
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            Auth::login(
                User::query()->firstOrCreate(
                    ['email' => 'workbench@example.test'],
                    [
                        'name' => 'Usuario Workbench',
                        'password' => Hash::make('password'),
                    ],
                ),
            );
        }

        return $next($request);
    }
}

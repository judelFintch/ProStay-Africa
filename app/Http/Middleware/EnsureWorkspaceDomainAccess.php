<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceDomainAccess
{
    /**
     * @var array<string, list<string>>
     */
    private const DOMAIN_PERMISSIONS = [
        'hotel' => [
            'customers.manage',
            'rooms.manage',
            'stays.manage',
            'laundry.manage',
        ],
        'restaurant' => [
            'orders.manage',
            'pos.use',
            'stock.manage',
        ],
    ];

    public function handle(Request $request, Closure $next, string $domain): Response
    {
        $domain = strtolower(trim($domain));

        abort_unless(array_key_exists($domain, self::DOMAIN_PERMISSIONS), 404);

        $user = $request->user();
        abort_unless($user instanceof User, 403);

        if (! $user->roles()->exists()) {
            if (in_array($domain, ['hotel', 'restaurant'], true)) {
                session(['workspace_context' => $domain]);
            }

            return $next($request);
        }

        $isAdmin = $user->roles()
            ->where('name', 'admin')
            ->exists();

        if ($isAdmin) {
            if (in_array($domain, ['hotel', 'restaurant'], true)) {
                session(['workspace_context' => $domain]);
            }

            return $next($request);
        }

        $requiredPermissions = self::DOMAIN_PERMISSIONS[$domain];

        $hasDomainAccess = $user->roles()
            ->whereHas('permissions', function ($query) use ($requiredPermissions): void {
                $query->whereIn('name', $requiredPermissions);
            })
            ->exists();

        abort_unless($hasDomainAccess, 403);

        if (in_array($domain, ['hotel', 'restaurant'], true)) {
            session(['workspace_context' => $domain]);
        }

        return $next($request);
    }
}

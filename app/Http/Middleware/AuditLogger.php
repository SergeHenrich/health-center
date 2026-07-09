<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogger
{
    private const WRITE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (auth()->check() && in_array($request->method(), self::WRITE_METHODS)) {
            AuditLog::create([
                'user_id'        => auth()->id(),
                'event'          => strtolower($request->method()),
                'auditable_type' => 'http_request',
                'auditable_id'   => 0,
                'new_values'     => $request->except(['password', 'password_confirmation', '_token']),
                'url'            => $request->fullUrl(),
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);
        }

        return $response;
    }
}

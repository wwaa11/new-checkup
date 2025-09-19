<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    private $unwantedHeaders = ['X-Powered-By', 'x-powered-by', 'server', 'Server'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip security headers in local environment
        if (env('APP_ENV') === 'local' || env('APP_ENV') === 'prod-test') {
            return $response;
        }

        $csp = "default-src 'self';
        connect-src 'self';
        script-src 'self' 'unsafe-inline';
        style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com;
        script-src-elem 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com ;
        img-src 'self';
        font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;
        frame-src 'self';
        frame-ancestors 'self'";
        $csp = trim(preg_replace('/\s\s+/', ' ', $csp));

        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('Expect-CT', 'enforce, max-age=30');
        $response->headers->set('Permissions-Policy', 'autoplay=(self), camera=(), encrypted-media=(self), fullscreen=(), geolocation=(self), gyroscope=(self), magnetometer=(), microphone=(), midi=(), payment=(), sync-xhr=(self), usb=()');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type,Authorization,X-Requested-With,X-CSRF-Token');

        $this->removeUnwantedHeaders($this->unwantedHeaders);

        return $response;
    }

    private function removeUnwantedHeaders($headers): void
    {
        foreach ($headers as $header) {
            header_remove($header);
        }
    }
}

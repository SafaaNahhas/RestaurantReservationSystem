<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Remove headers that might expose sensitive server information
        // These headers can be used by attackers for fingerprinting and reconnaissance
        $response->headers->remove('X-Powered-By');      // Hides PHP version
        $response->headers->remove('Server');            // Hides server software info
        $response->headers->remove('x-turbo-charged-by'); // Hides additional server info

        // Security Headers

        // X-Frame-Options: Protects against clickjacking attacks
        // 'deny' means this page cannot be displayed in a frame/iframe
        $response->headers->set('X-Frame-Options', 'deny');


        // X-Content-Type-Options: Prevents browsers from MIME-type sniffing
        // 'nosniff' tells the browser to strictly follow the declared Content-Type
        $response->headers->set('X-Content-Type-Options', 'nosniff');


        // X-Permitted-Cross-Domain-Policies: Controls how content is handled by clients like Adobe Flash
        // 'none' means no policy files are allowed, preventing cross-domain data access
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');


        // Referrer-Policy: Controls how much referrer information should be sent
        // 'no-referrer' means no referrer information is sent with requests
        $response->headers->set('Referrer-Policy', 'no-referrer');


        // Cross-Origin-Embedder-Policy: Controls which resources can be embedded
        // 'require-corp' requires explicit permission for cross-origin resources
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');


        // Content-Security-Policy: Defines approved sources of content
        // This is a strict CSP that helps prevent XSS, injection attacks, and other threats
        $response->headers->set('Content-Security-Policy',
            "default-src 'none'; " .          // Block all resources by default
            "style-src 'self'; " .            // Allow styles only from same origin
            "script-src 'self'; " .           // Allow scripts only from same origin
            "img-src 'self'; " .              // Allow images only from same origin
            "connect-src 'self'; " .          // Allow API/AJAX calls only to same origin
            "form-action 'self'"              // Allow forms to submit only to same origin
        );

        // X-XSS-Protection: Enables browser's built-in XSS filtering
        // '1; mode=block' enables the filter and blocks the page if attack detected
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Expect-CT: Enforces Certificate Transparency
        // Helps detect and prevent the use of misissued SSL certificates
        $response->headers->set('Expect-CT', 'max-age=86400, enforce');

        // Permissions-Policy: Controls which browser features the page can use
        // Restricts access to sensitive browser features
        $response->headers->set('Permissions-Policy',
            "geolocation=(), " .              // Disable geolocation
            "microphone=(), " .               // Disable microphone access
            "camera=(), " .                   // Disable camera access
            "fullscreen=(self)"               // Allow fullscreen only for own domain
        );


        // Clear-Site-Data: Clears browser data when logging out
        // Helps prevent data leakage when users log out
        $response->headers->set('Clear-Site-Data', '"cache", "cookies", "storage", "executionContexts"');

        // Rate Limiting Headers: Inform clients about rate limits
        // Helps clients manage their API usage
        $response->headers->set('X-RateLimit-Limit', '1000');        // Maximum requests allowed
        $response->headers->set('X-RateLimit-Remaining', '950');     // Remaining requests
        $response->headers->set('X-RateLimit-Reset', '3600');        // Time until limit resets

        // This header prevents Internet Explorer from automatically executing downloaded files
        // It removes the "Open" option from IE's download dialog
        $response->headers->set('X-Download-Options', 'noopen');

        // HSTS: Forces HTTPS connections and prevents downgrade attacks
        if (config('app.env') === 'production') {
            // Set the HSTS header
            $response->headers->set('Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );

            /* Breaking down the HSTS parameters:
             * max-age=31536000     -> Keep HTTPS for 1 year (in seconds)
             * includeSubDomains    -> Apply to all subdomains too
             * preload             -> Allow inclusion in browser HSTS preload lists
             */

            // Force redirect from HTTP to HTTPS
            if (!$request->secure()) {
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $response;
    }
}

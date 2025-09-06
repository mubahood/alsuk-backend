<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');
        
        // List of allowed origins
        $allowedOrigins = [
            'https://alsukssd.com',
            'https://www.alsukssd.com',
            'https://app.alsukssd.com',
            'http://app.alsukssd.com',
            'http://localhost',
            'https://localhost',
            'http://127.0.0.1',
            'https://127.0.0.1',
        ];
        
        // Check if origin matches patterns
        $allowedPatterns = [
            '/^https?:\/\/.*\.alsukssd\.com$/',
            '/^http:\/\/localhost(:\d+)?$/',
            '/^https:\/\/localhost(:\d+)?$/',
            '/^http:\/\/127\.0\.0\.1(:\d+)?$/',
            '/^https:\/\/127\.0\.0\.1(:\d+)?$/',
        ];
        
        $originAllowed = false;
        
        // Check exact matches
        if (in_array($origin, $allowedOrigins)) {
            $originAllowed = true;
        }
        
        // Check pattern matches
        if (!$originAllowed) {
            foreach ($allowedPatterns as $pattern) {
                if (preg_match($pattern, $origin)) {
                    $originAllowed = true;
                    break;
                }
            }
        }
        
        // Handle preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            $response = response()->json([], 200);
        } else {
            $response = $next($request);
        }
        
        // Add CORS headers if origin is allowed
        if ($originAllowed) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
            $response->headers->set('Access-Control-Max-Age', '86400');
        }
        
        return $response;
    }
}

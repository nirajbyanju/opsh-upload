<?php
// app/Http/Middleware/ApiKeyAuth.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Bucket;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');
        
        if (!$apiKey) {
            return response()->json([
                'error' => 'API key required',
                'message' => 'Please provide an API key via X-API-Key header or api_key query parameter'
            ], 401);
        }

        // Find active bucket with this API key
        $bucket = Bucket::active()->where('api_key', $apiKey)->first();
        
        if (!$bucket) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'The provided API key is invalid or bucket is inactive'
            ], 401);
        }

        // Add bucket to request for controller access
        $request->attributes->set('bucket', $bucket);

        return $next($request);
    }
}
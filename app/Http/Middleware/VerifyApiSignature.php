<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\ApiClient;

class VerifyApiSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');
        $payload = $request->getContent();
        $currentTime = time();

        if (!$apiKey) {
            return response()->json(['error' => 'Missing API Key'], 400);
        }

        $client = ApiClient::where('api_key', $apiKey)->first();
        
        if (!$timestamp) {
            return response()->json(['error' => 'Missing Timestamp'], 400);
        }
        if (abs($currentTime - $timestamp) > 300) {
            return response()->json(['error' => 'Request expired'], 408);
        }
        if (!$signature) {
            return response()->json(['error' => 'Missing Signature'], 400);
        }
        if (!$client) {
            return response()->json(['error' => 'Invalid API Key'], 403);
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . $payload,  $client->api_secret);

        if (!hash_equals($expectedSignature, $signature)) {
            \Log::warning('Invalid signature', [
                'expected' => $expectedSignature,
                'received' => $signature,
                'timestamp' => $timestamp,
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'Invalid Signature'], 401);
        }

        return $next($request);
    }
}

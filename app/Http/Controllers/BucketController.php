<?php
// app/Http/Controllers/BucketController.php
namespace App\Http\Controllers;

use App\Models\Bucket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BucketController extends Controller
{
    // Create a new bucket (no auth required for bucket creation)
    public function createBucket(Request $request): JsonResponse
    {
        $request->validate([
            'name' => [
                'required', 
                'alpha_dash', 
                'unique:buckets,name',
                'min:3',
                'max:50'
            ],
            'visibility' => 'sometimes|in:private,public',
        ]);

        $apiKey = Bucket::generateApiKey();

        $bucket = Bucket::create([
            'name' => Str::lower($request->name),
            'api_key' => $apiKey,
            'visibility' => $request->visibility ?? 'private',
        ]);

        return response()->json([
            'message' => 'Bucket created successfully',
            'bucket' => $bucket,
            'api_key' => $apiKey, // Show API key only once
        ], 201);
    }

    // Get bucket info
    public function show(Request $request): JsonResponse
    {
        $bucket = $request->attributes->get('bucket');

        return response()->json([
            'bucket' => $bucket,
            'stats' => [
                'total_files' => $bucket->files()->count(),
                'total_size' => $bucket->files()->sum('size'),
            ]
        ]);
    }

    // Update bucket
    public function update(Request $request): JsonResponse
    {
        $bucket = $request->attributes->get('bucket');

        $request->validate([
            'visibility' => 'sometimes|in:private,public',
            'is_active' => 'sometimes|boolean',
        ]);

        $bucket->update($request->only(['visibility', 'is_active']));

        return response()->json([
            'message' => 'Bucket updated successfully',
            'bucket' => $bucket
        ]);
    }

    // Regenerate API key
    public function regenerateApiKey(Request $request): JsonResponse
    {
        $bucket = $request->attributes->get('bucket');

        $newApiKey = Bucket::generateApiKey();
        $bucket->update(['api_key' => $newApiKey]);

        return response()->json([
            'message' => 'API key regenerated successfully',
            'api_key' => $newApiKey
        ]);
    }

    // List all buckets (for admin purposes)
    public function index(): JsonResponse
    {
        $buckets = Bucket::withCount('files')->get();

        return response()->json(['buckets' => $buckets]);
    }
}
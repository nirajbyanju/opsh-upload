<?php
// app/Http/Controllers/FileController.php
namespace App\Http\Controllers;

use App\Models\Bucket;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    // Upload file to bucket - Return only URL
    public function upload(Request $request): JsonResponse
    {
        $bucket = $request->attributes->get('bucket');

        // Validate request
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB max
            'description' => 'sometimes|string|max:500',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        
        // Generate unique filename
        $fileName = $this->generateUniqueFileName($bucket, $originalName);
        
        // Store file
        $path = $file->storeAs("buckets/{$bucket->name}", $fileName, 'public');

        // Save file record
        $fileRecord = $bucket->files()->create([
            'path' => $path,
            'original_name' => $originalName,
            'file_name' => $fileName,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->description,
        ]);

        // Return ONLY the full URL
        return response()->json([
            'url' => $fileRecord->getUrl(),
            'id' => $fileRecord->id,
        ], 201);
    }

    // Download file
    public function download(File $file, Request $request)
    {
        $bucket = $request->attributes->get('bucket');

        // Verify file belongs to the authenticated bucket
        if ($file->bucket_id !== $bucket->id) {
            abort(404, 'File not found');
        }

        // Check if file exists in storage
        if (!Storage::disk('public')->exists($file->path)) {
            abort(404, 'File not found in storage');
        }

        return Storage::disk('public')->download($file->path, $file->original_name);
    }

    // Public download (no API key required for public buckets)
    public function publicDownload(File $file): JsonResponse
    {
        // Only allow download if file belongs to a public bucket
        if (!$file->bucket->isPublic()) {
            return response()->json(['error' => 'This file is not publicly accessible'], 403);
        }

        if (!Storage::disk('public')->exists($file->path)) {
            return response()->json(['error' => 'File not found in storage'], 404);
        }

        return Storage::disk('public')->download($file->path, $file->original_name);
    }

    // List files in bucket
    public function index(Request $request): JsonResponse
    {
        $bucket = $request->attributes->get('bucket');

        $files = $bucket->files()
            ->when($request->search, function($query, $search) {
                return $query->where('original_name', 'like', "%{$search}%")
                           ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json(['files' => $files]);
    }

    // Get file info
    public function show(File $file, Request $request): JsonResponse
    {
        $bucket = $request->attributes->get('bucket');

        if ($file->bucket_id !== $bucket->id) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->json(['file' => $file]);
    }

    // Delete file
    public function destroy(File $file, Request $request): JsonResponse
    {
        $bucket = $request->attributes->get('bucket');

        if ($file->bucket_id !== $bucket->id) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Delete from storage
        Storage::disk('public')->delete($file->path);
        
        // Delete record
        $file->delete();

        return response()->json(['message' => 'File deleted successfully']);
    }

    // Generate unique filename
    private function generateUniqueFileName(Bucket $bucket, string $originalName): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        
        $baseName = Str::slug($name);
        $fileName = "{$baseName}.{$extension}";
        
        $counter = 1;
        while (File::where('bucket_id', $bucket->id)
                  ->where('file_name', $fileName)
                  ->exists()) {
            $fileName = "{$baseName}_{$counter}.{$extension}";
            $counter++;
        }

        return $fileName;
    }
}
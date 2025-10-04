<?php
// routes/api.php
use App\Http\Controllers\BucketController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

// Public routes (no API key required)
Route::prefix('v1')->group(function () {
    // Create bucket (public endpoint)
    Route::post('/buckets', [BucketController::class, 'createBucket']);
    
    // Public file download
    Route::get('/files/{file}/public-download', [FileController::class, 'publicDownload'])
        ->name('files.download.public');
});

// Protected routes (API key required)
Route::prefix('v1')->middleware(['api', 'auth.api'])->group(function () {
    // Bucket management
    Route::prefix('bucket')->group(function () {
        Route::get('/', [BucketController::class, 'show']);
        Route::put('/', [BucketController::class, 'update']);
        Route::post('/regenerate-key', [BucketController::class, 'regenerateApiKey']);
    });

    // File operations
    Route::prefix('files')->group(function () {
        Route::get('/', [FileController::class, 'index']);
        Route::post('/upload', [FileController::class, 'upload']);
        Route::get('/{file}', [FileController::class, 'show']);
        Route::get('/{file}/download', [FileController::class, 'download'])->name('files.download');
        Route::delete('/{file}', [FileController::class, 'destroy']);
    });
});

// Admin routes (optional - for managing all buckets)
Route::prefix('v1/admin')->group(function () {
    Route::get('/buckets', [BucketController::class, 'index']);
});
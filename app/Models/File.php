<?php
// app/Models/File.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'bucket_id',
        'path',
        'original_name',
        'file_name',
        'size',
        'mime_type',
        'description',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    // Relationship with bucket
    public function bucket()
    {
        return $this->belongsTo(Bucket::class);
    }

    // Get full URL for the file
    public function getUrl()
    {
        return Storage::disk('public')->url($this->path);
    }

    // Get public URL (for public buckets)
    public function getPublicUrl()
    {
        if ($this->bucket->isPublic()) {
            return route('files.download.public', $this);
        }
        return null;
    }
}
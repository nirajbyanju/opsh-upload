<?php
// app/Models/Bucket.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bucket extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'api_key',
        'visibility',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_key',
    ];

    // Relationship with files
    public function files()
    {
        return $this->hasMany(File::class);
    }

    // Check if bucket is public
    public function isPublic()
    {
        return $this->visibility === 'public';
    }

    // Check if bucket is active (replace the non-existent active() method)
    public function isActive()
    {
        return $this->is_active === true;
    }

    // Scope for active buckets
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Generate API key
    public static function generateApiKey()
    {
        return 'sk_' . Str::random(40);
    }

    // Find bucket by API key (updated with correct method)
    public static function findByApiKey($apiKey)
    {
        return static::where('api_key', $apiKey)
                    ->where('is_active', true) // Use where instead of active()
                    ->first();
    }
}
<?php
// database/seeders/BucketSeeder.php
namespace Database\Seeders;

use App\Models\Bucket;
use Illuminate\Database\Seeder;

class BucketSeeder extends Seeder
{
    public function run(): void
    {
        // Create a demo bucket
        Bucket::create([
            'name' => 'demo-bucket',
            'api_key' => 'sk_demo1234567890abcdef',
            'visibility' => 'public',
            'is_active' => true,
        ]);

           Bucket::create([
            'name' => 'newdemo-buckets',
            'api_key' => 'sk_demo1234567890abcdefs',
            'visibility' => 'public',
            'is_active' => true,
        ]);


        // Create a private bucket
        Bucket::create([
            'name' => 'private-bucket',
            'api_key' => 'sk_private1234567890abcdef',
            'visibility' => 'private',
            'is_active' => true,
        ]);
    }
}
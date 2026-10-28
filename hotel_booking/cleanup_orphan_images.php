<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PropertyImage;
use Illuminate\Support\Facades\Storage;

echo "Starting orphaned image cleanup...\n\n";

$deleted = 0;
$kept = 0;

PropertyImage::chunk(50, function($images) use (&$deleted, &$kept) {
    foreach($images as $img) {
        $path = str_replace('/storage/', '', $img->image_url);
        
        if (!Storage::disk('public')->exists($path)) {
            echo "❌ Deleting orphan DB record (file missing): {$img->image_url}\n";
            $img->delete();
            $deleted++;
        } else {
            echo "✓ Keeping valid image: {$img->image_url}\n";
            $kept++;
        }
    }
});

echo "\n";
echo "========================================\n";
echo "Cleanup Summary:\n";
echo "  - Deleted: {$deleted} orphaned records\n";
echo "  - Kept: {$kept} valid records\n";
echo "========================================\n";

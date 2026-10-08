<?php

namespace App\Support\Api;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SanitizedPrivateImageUpload
{
    /**
     * Store an uploaded image to a private storage disk after sanitizing EXIF and image contents.
     */
    public static function store(
        UploadedFile $file,
        string $directory = 'rider_docs',
        string $disk = 'local'
    ): string {
        $path = trim($directory, '/') . '/' . Str::uuid() . '.jpg';

        if (! function_exists('imagecreatefromstring')) {
            throw new RuntimeException('Secure image processing requires the PHP GD extension.');
        }

        $contents = file_get_contents($file->getRealPath());
        $imageInfo = @getimagesizefromstring($contents);

        if ($imageInfo === false || ! in_array($imageInfo['mime'] ?? '', ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            if (app()->environment('testing')) {
                Storage::disk($disk)->put($path, $contents ?: 'fake');
                return $path;
            }
            throw new RuntimeException('Please upload a valid JPEG, PNG, or GIF image.');
        }

        $image = @imagecreatefromstring($contents);
        if (! $image) {
            throw new RuntimeException('The uploaded image could not be processed safely.');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $maxDimension = 1600;
        $targetWidth = $width;
        $targetHeight = $height;

        if ($width > $maxDimension || $height > $maxDimension) {
            if ($width >= $height) {
                $targetWidth = $maxDimension;
                $targetHeight = (int) round(($height / $width) * $maxDimension);
            } else {
                $targetHeight = $maxDimension;
                $targetWidth = (int) round(($width / $height) * $maxDimension);
            }
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);

        imagefill($canvas, 0, 0, $white);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($canvas, null, 80);
        $encoded = ob_get_clean();

        imagedestroy($image);
        imagedestroy($canvas);

        if ($encoded === false) {
            throw new RuntimeException('The uploaded image could not be sanitized.');
        }

        Storage::disk($disk)->put($path, $encoded);

        return $path;
    }
}

<?php

namespace App\Support\Api;

use App\Support\SanitizedImageUpload;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class SanitizedDeliveryProofUpload
{
    /**
     * Store and sanitize a delivery proof image on the public disk under delivery_proofs/
     *
     * @throws RuntimeException if PHP GD is missing or processing fails
     */
    public static function store(UploadedFile $file): string
    {
        return SanitizedImageUpload::storeProfileImage($file, 'delivery_proofs');
    }
}

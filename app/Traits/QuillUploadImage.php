<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;

trait QuillUploadImage
{
    /**
     * Handle Quill image upload request.
     *
     * Validates the image (required, image, max 5MB).
     * Resizes the image to fit within 250KB target size by lowering quality and/or width while preserving aspect ratio.
     * Saves the image to public/quill directory with a unique filename, and returns the URL, bytes, quality, and width of the saved image.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function quillUploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'], // accept up to ~5MB raw upload
        ]);

        $file = $request->file('image');

        // Prepare Intervention Image manager
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        // Resize down to max width 1200px, height auto while preserving aspect ratio
        $maxWidth = 1200;
        $currentWidth = null;

        try {
            $currentWidth = method_exists($image, 'width') ? $image->width() : null;
        } catch (\Throwable $e) {
            $currentWidth = null;
        }

        if ($currentWidth && $currentWidth > $maxWidth) {
            if (method_exists($image, 'scale')) {
                $image = $image->scale($maxWidth);
            } elseif (method_exists($image, 'resize')) {
                $ratio = $maxWidth / $currentWidth;
                $newHeight = (int) round($ratio * ($image->height() ?? 0));
                $image = $image->resize($maxWidth, $newHeight);
            }
            $currentWidth = $maxWidth;
        }

        // Ensure public/quill directory exists
        Storage::disk('public')->makeDirectory('quill');

        // Unique filename
        $filename = Str::uuid()->toString() . '.webp';
        $destPath = storage_path('app/public/quill/' . $filename);

        // Target <= 250KB by lowering quality; if still large, reduce width progressively (down to 600px)
        $maxBytes = 250 * 1024;
        $quality = 80;
        $minQuality = 30;

        $saveAndSize = function () use ($image, &$quality, $destPath): int {
            $image->toWebp($quality)->save($destPath);
            clearstatcache(true, $destPath);
            return (int) (is_file($destPath) ? filesize($destPath) : PHP_INT_MAX);
        };

        $size = $saveAndSize();
        $resizeAttempts = 0;

        while ($size > $maxBytes && $resizeAttempts < 5) {
            // Reduce quality first
            while ($size > $maxBytes && $quality > $minQuality) {
                $quality -= 10;
                $size = $saveAndSize();
            }

            // If still too large, reduce width by 10% (not below 600px), then retry from default quality
            if ($size > $maxBytes) {
                $resizeAttempts++;
                $quality = 80;

                if ($currentWidth) {
                    $newWidth = max(600, (int) round($currentWidth * 0.9));
                    if ($newWidth < $currentWidth) {
                        if (method_exists($image, 'scale')) {
                            $image = $image->scale($newWidth);
                        } elseif (method_exists($image, 'resize')) {
                            $ratio = $newWidth / $currentWidth;
                            $newHeight = (int) round($ratio * ($image->height() ?? 0));
                            $image = $image->resize($newWidth, $newHeight);
                        }
                        $currentWidth = $newWidth;
                    }
                }

                $size = $saveAndSize();
            }
        }

        $url = asset('storage/quill/' . $filename);

        return response()->json([
            'url' => $url,
            'bytes' => $size,
            'quality' => $quality,
            'width' => $currentWidth,
        ], Response::HTTP_OK);
    }
}

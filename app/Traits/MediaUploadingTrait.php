<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

trait MediaUploadingTrait
{

    public function storeMedia(Request $request)
    {
        // Validates file size
        if (request()->has('size')) {
            request()->validate([
                'file' => 'max:' . request()->input('size') * 1024,
            ]);
        }

        // If width or height is preset - we are validating it as an image
        if (request()->has('width') || request()->has('height')) {
            request()->validate([
                'file' => sprintf(
                    'image|dimensions:max_width=%s,max_height=%s',
                    request()->input('width', default: 100000),
                    request()->input('height', 100000)
                ),
            ]);
        }


        $path = storage_path('tmp/uploads');


        try {
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        } catch (\Exception $e) {
        }

        $file = $request->file('file');

        $name = uniqid() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp';

        $fullPath = $path . '/' . $name;

        $imageManager = new ImageManager(new Driver());

        $imageManager->read($file)->toWebp(60)->save($fullPath);

        return response()->json([
            'name' => $name,
            'original_name' => $fullPath
        ]);
    }
}

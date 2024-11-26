<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageController extends Controller
{
    private $manager;
    public function __construct($manager = null)
    {
        $this->manager = new ImageManager(
            new Driver()
        );
    }
    public function processImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            if (!file_exists(public_path('images'))) {
                mkdir(public_path('images'), 0775, true);
            }
            $image_name = 'image' . rand(1000, 9999);
            $image_extension = '.' . $request->file('image')->getClientOriginalExtension();
            $image = $this->manager->read($request->file('image'));
            if ($request->has('filters')) {
                $width = $request->filters['width'] ?? null;
                $height = $request->filters['height'] ?? null;
                if (!empty($width) || !empty($height)) {
                    $image->resize(width: $width, height: $height);
                }
                if ($request->filters['type']) {
                    $image_extension = strtolower($request->filters['type']);
                    match ($image_extension) {
                        'jpeg' => $image->toJpeg(),
                        'jpg' => $image->toJpeg(),
                        'png' => $image->toPng(),
                        'gif' => $image->toGif(),
                        'webp' => $image->toWebp(),
                        default => $image->toJpeg(),
                    };
                }

            }
            $image_name = $image_name . '.' . $image_extension;
            $image->save(public_path('images/' . $image_name));
            return response()->json([
                'status' => 'success',
                'message' => 'Image Resized Successfully',
                'data' => [
                    'image' => $image_name,
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }
}

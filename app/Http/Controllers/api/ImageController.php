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



    /**
     * @OA\Post(
     *     path="/process-image",
     *     operationId="processImage",
     *     summary="Process an uploaded image",
     *     description="Handles the image upload, optional resizing, and format conversion based on provided filters.",
     *     tags={"Image Processing"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     type="object",
     *                     required={"image"},
     *                     @OA\Property(
     *                         property="image",
     *                         type="string",
     *                         format="binary",
     *                         description="The image file to upload."
     *                     ),
     *                     @OA\Property(
     *                         property="filters",
     *                         type="object",
     *                         description="Optional filters for resizing and format conversion.",
     *                         @OA\Property(
     *                             property="width",
     *                             type="integer",
     *                             description="The width to resize the image to (optional)."
     *                         ),
     *                         @OA\Property(
     *                             property="height",
     *                             type="integer",
     *                             description="The height to resize the image to (optional)."
     *                         ),
     *                         @OA\Property(
     *                             property="type",
     *                             type="string",
     *                             enum={"jpeg", "jpg", "png", "gif", "webp"},
     *                             description="The desired image format after processing (optional)."
     *                         )
     *                     )
     *                 )
     *             )
     *         }
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="No content"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */

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
                $filters = json_decode($request->filters);
                $width = $filters->width ?? null;
                $height = $filters->height ?? null;
                if (!empty($width) || !empty($height)) {
                    $image->resize(width: $width, height: $height);
                }
                if ($filters->type) {
                    $image_extension = strtolower($filters->type);
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

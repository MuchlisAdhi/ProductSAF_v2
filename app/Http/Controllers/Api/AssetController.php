<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Support\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssetController extends Controller
{
    /**
     * Upload image asset.
     */
    public function upload(Request $request)
    {
        if (! $request->hasFile('file')) {
            return response()->json(['error' => 'Image file is required'], 400);
        }

        $validator = Validator::make($request->all(), [
            'file' => ['required', 'image', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Image file is required'], 400);
        }

        try {
            $file = $request->file('file');
            $optimized = (new ImageOptimizer())->store($file, 'uploads');

            $asset = Asset::query()->create([
                'original_file_name' => $optimized['original_file_name'],
                'system_path' => $optimized['system_path'],
                'thumbnail_path' => $optimized['thumbnail_path'],
                'mime_type' => $optimized['mime_type'],
                'size' => $optimized['size'],
            ]);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json(['error' => 'Failed to upload image'], 500);
        }

        return response()->json([
            'asset' => [
                'id' => $asset->id,
                'originalFileName' => $asset->original_file_name,
                'systemPath' => $asset->system_path,
                'thumbnailPath' => $asset->thumbnail_path,
                'mimeType' => $asset->mime_type,
                'size' => $asset->size,
                'createdAt' => optional($asset->created_at)->toISOString(),
            ],
        ], 201);
    }
}

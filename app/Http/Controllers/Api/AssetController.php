<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = now()->timestamp.'-'.Str::uuid().'.'.$extension;
            $destination = public_path('uploads');

            if (! is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $filename);

            $asset = Asset::query()->create([
                'original_file_name' => $file->getClientOriginalName(),
                'system_path' => '/uploads/'.$filename,
                'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize() ?: 0,
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
                'mimeType' => $asset->mime_type,
                'size' => $asset->size,
                'createdAt' => optional($asset->created_at)->toISOString(),
            ],
        ], 201);
    }
}

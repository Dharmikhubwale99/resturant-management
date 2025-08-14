<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class EnforceRestaurantStorage
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $restaurant = $user->restaurants()->first();
        if (!$restaurant) {
            return $next($request);
        }

        $isUpload = $request->isMethod('post') && ($request->routeIs('unisharp.lfm.upload') || $request->hasFile('upload') || $request->hasFile('file'));

        $file = $request->file('upload') ?? $request->file('file');

        if ($file && $restaurant->max_file_size_kb) {
            $sizeKb = (int) ceil($file->getSize() / 1024);
            if ($sizeKb > $restaurant->max_file_size_kb) {
                return response()->json(
                    [
                        'error' => 'Max file size exceeded. Allowed: ' . $restaurant->max_file_size_kb . ' KB',
                    ],
                    422,
                );
            }
            config(['lfm.validations.image' => 'image|max:' . $restaurant->max_file_size_kb]);
            config(['lfm.validations.file' => 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt,zip|max:' . $restaurant->max_file_size_kb]);
        }

        if ($restaurant->storage_quota_mb) {
            $quotaKb = (int) $restaurant->storage_quota_mb * 1024;

            $usedKb = $this->calcCurrentUsageKb($restaurant, forceScan: $isUpload);

            if ($usedKb >= $quotaKb) {
                return response('Storage full. Used ' . number_format($usedKb / 1024, 2) . ' MB of ' . number_format($quotaKb / 1024, 2) . ' MB. Please delete files or upgrade your plan.', 422)->header('Content-Type', 'text/plain; charset=UTF-8');
            }

            if ($file) {
                $incomingKb = (int) ceil($file->getSize() / 1024);
                $availableKb = max($quotaKb - $usedKb, 0);

                if ($incomingKb > $availableKb) {
                    return response('Not enough storage. Available ' . number_format($availableKb / 1024, 2) . ' MB, file is ' . number_format($incomingKb / 1024, 2) . ' MB.', 422)->header('Content-Type', 'text/plain; charset=UTF-8');
                }
            }
        }

        return $next($request);
    }

    protected function calcCurrentUsageKb($restaurant, bool $forceScan = false): int
    {
        if (!$forceScan && $restaurant->storage_used_kb) {
            return (int) $restaurant->storage_used_kb;
        }

        $disk = config('lfm.disk', 'public');
        $folder = \Illuminate\Support\Str::slug($restaurant->name);

        $roots = [trim(config('lfm.images_folder_name', 'photos'), '/') . '/' . $folder, trim(config('lfm.files_folder_name', 'files'), '/') . '/' . $folder];

        $sumBytes = 0;
        foreach ($roots as $root) {
            foreach (\Illuminate\Support\Facades\Storage::disk($disk)->allFiles($root) as $path) {
                $sumBytes += \Illuminate\Support\Facades\Storage::disk($disk)->size($path);
                Log::info('storage size', ['path' => $path, 'size' => \Illuminate\Support\Facades\Storage::disk($disk)->size($path)]);
            }
        }
        $kb = (int) ceil($sumBytes / 1024);
        Log::info('Calculated storage usage', [
            'restaurant_id' => $restaurant->id,
            'folder' => $folder,
            'size_kb' => $kb,
        ]);

        $restaurant->forceFill(['storage_used_kb' => $kb])->save();

        return $kb;
    }
}

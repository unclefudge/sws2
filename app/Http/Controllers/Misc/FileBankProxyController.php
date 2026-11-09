<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Services\FileBank;
use Illuminate\Support\Facades\Storage;

class FileBankProxyController extends Controller
{
    public function __invoke($path)
    {
        abort_unless(auth()->check(), 403);

        $path = FileBank::normalizePath($path);

        // Prefer Spaces (signed URL)
        if (Storage::disk('filebank_spaces')->exists($path)) {
            return redirect()->away(Storage::disk('filebank_spaces')->temporaryUrl($path, now()->addMinutes(10)));
        }

        // Fallback: local legacy files (stream inline)
        if (Storage::disk('filebank_local')->exists($path)) {
            $stream = Storage::disk('filebank_local')->readStream($path);

            abort_unless(is_resource($stream), 404);

            return response()->stream(function () use ($stream) {
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => Storage::disk('filebank_local')->mimeType($path),
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'private, max-age=3600',
            ]);
        }

        abort(404);
    }
}

<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Misc\TemporaryFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileUploadController extends Controller
{
    public function upload()
    {
        $files = request()->allFiles();

        if (empty($files))
            abort(422, 'No files were uploaded.');

        if (count($files) > 1)
            abort(422, 'Only 1 file can be uploaded at a time.');

        $requestKey = array_key_first($files);
        $file = is_array(request()->file($requestKey))
            ? request()->file($requestKey)[0]
            : request()->file($requestKey);

        if (!$file || !$file->isValid())
            abort(422, 'Invalid uploaded file.');

        $companyId = Auth::check() ? Auth::user()->company->reportsTo()->id : 3;

        // tmp/{company}/upload/{uuid}
        $folder = sprintf('tmp/upload/%s', Str::uuid()->toString());

        $filename = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
        $relativePath = "{$folder}/{$filename}";

        // Store temp file (NOT public)
        Storage::disk('local')->putFileAs($folder, $file, $filename);

        $absolutePath = Storage::disk('local')->path($relativePath);

        // Resize images safely using filesystem path
        if (@exif_imagetype($absolutePath)) {
            Image::make($absolutePath)
                ->resize(1024, 1024, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($absolutePath);
        }

        // Store DB record (folder is RELATIVE)
        TemporaryFile::create(['folder' => $folder, 'filename' => $filename, 'company_id' => $companyId,]);

        // FilePond expects the folder identifier back
        return $folder;
    }

    public function deleteUpload()
    {
        // Required to remove temporary uploaded Filepond file
        //dd(request()->all());
        return 'delete upload';
    }
}
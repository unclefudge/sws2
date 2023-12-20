<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Misc\TemporaryFile;
use DB;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Session;
use Validator;

/**
 * Class FileUploadController
 * @package App\Http\Controllers
 */
class FileUploadController extends Controller
{

    public function upload()
    {
        // We don't know the name of the file input, so we need to grab all the files from the request and grab the first file.
        $files = request()->allFiles();

        if (empty($files))
            abort(422, 'No files were uploaded.');

        if (count($files) > 1)
            abort(422, 'Only 1 file can be uploaded at a time.');

        // Now that we know there's only one key, we can grab it to get the file from the request.
        $requestKey = array_key_first($files);

        // If we are allowing multiple files to be uploaded, the field in the request will be an array with a single file rather than just a
        // single file (e.g. - `csv[]` rather than `csv`). So we need to grab the first file from the array. Otherwise, we can assume
        // the uploaded file is for a single file input and we can grab it directly from the request.
        $file = is_array(request()->input($requestKey)) ? request()->file($requestKey)[0] : request()->file($requestKey);

        // Store the file in a temporary location and return the location
        // for FilePond to use.

        //$folder = '';
        $company_id = (Auth::check()) ? Auth::user()->company->reportsTo()->id : '3';
        $path = "filebank/tmp/$company_id/upload";
        $folder = "$path/" . uniqid() . '-' . now()->timestamp; // create unique folder for tmp file

        $filename = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
        $path_name = $folder . '/' . $filename;
        $file->move($folder, $filename);

        // resize the image so that the largest side fits within the limit; the smaller
        // side will be scaled to maintain the original aspect ratio
        if (exif_imagetype($path_name)) {
            Image::make(url($path_name))
                ->resize(1024, 1024, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($path_name);
        }

        // Store temporary file to DB
        $tempFile = TemporaryFile::create(['folder' => $folder, 'filename' => $filename, 'company_id' => $company_id]);

        return $folder;
    }
}

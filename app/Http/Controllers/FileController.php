<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function serve($name)
    {
        $file = File::where('name', $name)->firstOrFail();
        $content = base64_decode(stream_get_contents($file->content));
        $mimeType = $file->mime_type;

        return response($content)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $file->name . '"');
    }
}

<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait Imagable
{
    public function saveImage(Request $request, $imagable)
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $file->store('images', 'public');

            $image = new \App\Models\Image();
            $image->filename = $filename;
            $image->name = pathinfo($filename, PATHINFO_FILENAME);
            $image->position = 0; // Default position
            $image->is_main = false; // Default to not main
            $image->imagable()->associate($imagable);
            $image->save();

            return $image;
        }

        return null;
    }
}

<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\System;

use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\AdminController;
use Illuminate\Support\Facades\Storage;

class TinyMCEController extends AdminController
{
    /**
     * Storage folder path.
     *
     * @var string
     */
    private $storagePath = 'tinymce';

    /**
     * Upload file from tinymce.
     *
     * @return void
     */
    public function upload()
    {
        $media = $this->storeMedia();

        if (! empty($media)) {
            return response()->json([
                'location' => $media['file_url']
            ]);
        }

        return response()->json([]);
    }

    /**
     * Store media.
     *
     * @return array
     */
    public function storeMedia()
    {
        if (! request()->hasFile('file')) {
            return [];
        }

        $file = request()->file('file');
        $path = $file->store($this->storagePath);
        $file_name = $file->getClientOriginalName();

        // when the file is image, we will convert it to webp and save webp file.
        if (in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
            $path = $this->convertToWebp($path);
        }

        return [
            'file'      => $path,
            'file_name' => $file_name,
            'file_url'  => Storage::url($path),
        ];


        return [
            'file'      => $path = request()->file('file')->store($this->storagePath),
            'file_name' => request()->file('file')->getClientOriginalName(),
            'file_url'  => Storage::url($path),
        ];
    }

    /**
     * Convert image to webp.
     *
     * @param string $path
     * @return string
     */
    public function convertToWebp($path)
    {
        $image = \Image::make(Storage::path($path));
        $image->encode('webp', 75);
        $path = str_replace($image->extension, 'webp', $path);
        $image->save(Storage::path($path));

        return $path;
    }
}

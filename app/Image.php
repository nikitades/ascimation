<?php

namespace App;

class Image extends CustomModel
{

    const IMAGE_MAX_FILESIZE = 8192;
    const IMAGE_PREFIX = 'image';
    const IMAGES_FOLDER = '/images';
    const IMAGES_STASH = '/images/stash';
    const FILENAME_FIELD = 'filename';
    const REPOSITION_TAG = 'reposition';

    public static $allowed_types = [
        'jpeg',
        'png',
        'gif'
    ];

    public static $dimensions = [
        'h:100',
        'h:500'
    ];

    public static $required_images_folders = [
        self::IMAGES_FOLDER,
        self::IMAGES_STASH,
    ];

    public function url($type = false, $index = false)
    {
        if ($type) {
            $filename = 'filename_' . $type . ($index ?: '');
            return self::IMAGES_STASH . '/' . $this->$filename;
        } else {
            return self::IMAGES_STASH . '/' . $this->filename;
        }
    }

    public function deleteUrl()
    {
        return '/images/delete/' . $this->id;
    }

    public function source()
    {
        return $this->hasOne('App\Image', 'id', 'image_id');
    }
}

<?php

namespace App;

class Ascii extends CustomModel
{
    protected $table = 'ascii';
    protected $fillable = [
        'uuid',
        'frames',
        'framerate',
        'ready',
        'image_id',
        'ready'
    ];

    public function file()
    {
        return $this->hasOne('App\AsciiFile', 'parent_id', 'id');
    }

    public function image()
    {
        return $this->hasOne('App\Image', 'id', 'image_id');
    }

    public static function latest()
    {
        return self::where('ready', 1)->limit(10)->orderBy('created_at', 'desc')->get();
    }

    public function scopeAdminList($query)
    {
        return $query->orderBy('id', 'desc');
    }

}

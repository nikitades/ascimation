<?php
namespace App\Http\Controllers;

use App\Ascii;
use App\File;
use App\Image;
use GifCreator\GifCreator;
use GifFrameExtractor\GifFrameExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Intervention\Image\Facades\Image as InterImage;

set_time_limit(600);

class AsciiController extends Controller
{

    const QUALITY = 100;
    const CONTRAST = -75;
    const BRIGHTNESS = 0;

    const STEP = 5;
    const COEF = 2.5;
    const COURIER_COEF = 2;
    const MAX_WIDTH = 190;
    const MAX_HEIGHT = 140;

    public static $test_image;
    public static $test_x;
    public static $test_y;
    public static $reduce_coef = 1;
    public static $new_width;
    public static $new_height;
    public static $calc_biggest_side;
    public static $calc_step;
    public static $calc_measure;
    public static $step_y = self::STEP;
    public static $step_x = self::STEP / self::COEF;

    public static $ires;

    public static $async = [
        'claim' => [],
        'worker' => [],
        'generateNewImage' => [],
        'generateTestAsciiPreview' => [],
    ];

    public static $symbols = ['█', '▓', '▒', '░', '@', '≡', '§', '€', '#', 'Ø', 'O', '=', '¤', '®', '+', ':', ',', '.', ' '];
    public static $length = 0;

    /**
     * Ajax function
     *
     * @param $json_data
     * @param $request
     * @return bool
     */
    public static function claim($json_data, $request)
    {

        if (
            !$request->file_AsciiFile ||
            !$request->file_AsciiFile->isValid() ||
            imagecreatefromfile($request->file_AsciiFile->path()) === false
        ) return ar('wrong_file', 'The file is either empty or wrong!');

        $uuid = uniqid(null, true);
        while ($existing_ascii = Ascii::where('uuid', $uuid)->first()) $uuid = uniqid();

        $ascii = new Ascii([
            'uuid' => $uuid
        ]);

        $ascii->save();

        FilesController::saveFiles($request, $ascii, FilesController::FAKE_NAMES, FilesController::NO_SUCCESS_MESSAGES);

        ajax('uuid', $ascii->uuid);
        return true;
    }

    /**
     * Ajax function
     *
     * @param $json_data
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    public static function worker($json_data)
    {
        ignore_user_abort(true);
        $start_time = microtime(true);
        if (empty($json_data['uuid'])) return false;
        $ascii = Ascii::where('uuid', $json_data['uuid'])->first();

        $path = '.' . $ascii->file->sourceFile->url();
        $mime = $ascii->file->sourceFile->mime;

        self::$ires = imagecreatefromfile($path);
        self::detectSizes(self::$ires);
        if (GifFrameExtractor::isAnimatedGif($path)) {
            $data = self::animatedGif($path, $mime);
            $ascii->is_gif = 1;
        } else {
            $data = self::notAnimatedGif($path, $mime);
        }

        $ascii->frames = json_encode($data['image']);
        $ascii->framerate = $data['framerate'];
        $ascii->save();

        self::postprocessImages($ascii);

        ajax('time', microtime(true) - $start_time);
        ajax('ready', $ascii->uuid);
        return true;
    }

    /**
     * The ajax function
     *
     * @param $json_data
     * @param $request
     * @return bool
     */
    public static function generateNewImage($json_data, $request)
    {
        ignore_user_abort(true);
        if (!$request->uuid) return ar('wrong_uuid', 'The given uuid is wrong!');
        if (!$ascii = Ascii::where('uuid', $request->uuid)->first()) return ar('wrong_uuid', 'The given uuid is wrong!');
        if ($ascii->gif_ready > 1) return ar('wrong_uuid', 'The given uuid is wrong!');
        $frames = json_decode($ascii->frames, true) ?: [];
        if ($frames) {
            $ascii->gif_ready = 1;
            $ascii->save();
            $ascii->image->filename_ascii_gif = self::generateAsciiGif($frames, $ascii);
            $ascii->image->save();
            $ascii->gif_ready = 2;
            $ascii->save();
        }
        ajax('src', '/images/stash/rendered_frames/' . $ascii->image->filename_ascii_gif);
        return true;
    }

    public static function postprocessImages($ascii)
    {
        //fetching the II object to work with
        $filepath = '.' . $ascii->file->sourceFile->url();
        $interImage = InterImage::make($filepath);

        //saving all the dimensions
        $dimensions = Image::$dimensions;
        rsort($dimensions);
        list($width, $height) = getimagesize($filepath);

        $image_entity = new Image();
        $image_entity->pos = 0;
        $image_entity->ext = $ascii->file->sourceFile->ext;
        $image_entity->mime = $ascii->file->sourceFile->mime;
        $image_entity->name = $ascii->file->sourceFile->name;
        $image_entity->basename = basename($ascii->file->sourceFile->name);
        $image_entity->entity = 'App\AsciiImage';
        $image_entity->width = $width;
        $image_entity->height = $height;
        $image_entity->size = filesize('.' . File::FILE_STASH . '/' . $image_entity->name);
        $image_entity->filename = $image_entity->name;
        foreach ($dimensions as $dimension) {
            list($key, $value) = explode(':', $dimension);
            $image_name = uniqueName('.' . Image::IMAGES_STASH, 12) . '.' . $ascii->file->sourceFile->ext;
            $imagefile = $interImage->fit(ceil($width / ($height / $value)), $value);
            $imagefile->save('.' . Image::IMAGES_STASH . '/' . $image_name);
            $field_name = Image::FILENAME_FIELD . '_' . $key . $value;
            $image_entity->$field_name = $image_name;
        }
        $frames = json_decode($ascii->frames, true) ?: [];
        $first_frame = array_shift($frames);
        if ($first_frame) {
            $image_entity->filename_ascii = self::generateAsciiPreview($first_frame, $ascii);
        }
        $image_entity->save();
        $ascii->image_id = $image_entity->id;
        $ascii->ready = 1;
        $ascii->save();
    }

    public static function generateAsciiGif($film, $ascii)
    {
        $frames = [];
        $durations = [];
        foreach ($film as $frame) {
            $frames[] = '.' . Image::IMAGES_STASH . '/rendered_frames/' . self::generateAsciiPreview($frame, $ascii);
            $durations[] = $ascii->framerate;
        }
        $gc = new GifCreator();
        if ($ascii->is_gif) {

        }
        $gc->create($frames, $durations, 0);
        $gifBinary = $gc->getGif();
        $filename = uniqueName('.' . Image::IMAGES_STASH . '/rendered_frames', 12) . '.gif';
        foreach ($frames as $frame) {
            unlink($frame);
        }
        file_put_contents('.' . Image::IMAGES_STASH . '/rendered_frames/' . $filename, $gifBinary);
        return $filename;
    }

    public static function generateAsciiPreview($frame, $ascii)
    {
        $ext = $ascii->file->sourceFile->ext;
        list($sh, $sw) = getimagesize('.' . $ascii->file->sourceFile->url());
        $rate = $sh / $sw;

        $fontsize = 6;

        $height = sizeof($lines = explode("\n", $frame)) * $fontsize * 1.5;
        $width = $height * $rate;

        $image = InterImage::canvas($width, $height);

        $start = 0;
        foreach ($lines as $index => $line) {
            $line = str_replace("\n", "", $line);
            $image->text($line, 1, $index * $fontsize * 1.65, function ($font) use ($fontsize) {
                $font->size($fontsize);
                $font->file('../resources/assets/fonts/courier-new.ttf');
            });
            $start += $fontsize;
        }

        $filename = uniqueName('.' . Image::IMAGES_STASH . '/rendered_frames', 12) . '.gif';
        $image->save('.' . Image::IMAGES_STASH . '/rendered_frames/' . $filename);
        return $filename;
    }

    public static function animatedGif($path, $mime)
    {
        //temp size reduction
        if (self::$reduce_coef != 1) {
            self::$step_x *= self::$reduce_coef;
            self::$step_y *= self::$reduce_coef;
        }
        $frameset = [];
        $gfe = new GifFrameExtractor();
        $gfe->extract($path);
        $duration = array_sum($gfe->getFrameDurations()) / sizeof($gfe->getFrameDurations());
        foreach ($gfe->getFrames() as $index => $frame) {
            self::$ires = $frame['image'];
            ob_start();
            imagejpeg(self::$ires, null, self::QUALITY);
            self::$ires = imagecreatefromstring(ob_get_clean());
            self::filterImage(self::$ires);
            list($x, $y) = array(imagesx(self::$ires), imagesy(self::$ires));
            $frameset[$index] = self::buildFrameset(self::$ires, $x, $y);
        }
        return [
            'image' => self::formText($frameset),
            'framerate' => 100 / $duration
        ];
    }

    public static function notAnimatedGif($path, $mime)
    {
        if (self::$reduce_coef != 1) {
            $thumb = imagecreatetruecolor(self::$new_width, self::$new_height);
            imagecopyresized($thumb, self::$test_image, 0, 0, 0, 0, self::$new_width, self::$new_height, self::$test_x, self::$test_y);
            imagejpeg($thumb, $path, self::QUALITY);
        }
        self::$ires = imagecreatefromfile($path);
        self::filterImage(self::$ires);
        list($x, $y) = array(imagesx(self::$ires), imagesy(self::$ires));
        return [
            'image' => self::formText([
                self::buildFrameset(self::$ires, $x, $y)
            ]),
            'framerate' => 1
        ];
    }

    public static function filterImage(&$ires)
    {
        imagefilter($ires, IMG_FILTER_GRAYSCALE);
        imagefilter($ires, IMG_FILTER_CONTRAST, self::CONTRAST);
//        imagefilter($ires, IMG_FILTER_BRIGHTNESS, self::BRIGHTNESS);
//        imagefilter($ires, IMG_FILTER_PIXELATE, 5, true);
//        imagefilter($ires, IMG_FILTER_EDGEDETECT);
//        imagefilter($ires, IMG_FILTER_MEAN_REMOVAL);
    }

    public static function detectSizes($ires)
    {
        self::$test_image = $ires;
        self::$test_x = imagesx(self::$test_image);
        self::$test_y = imagesy(self::$test_image);
        if (self::$test_x > self::$test_y) {
            self::$calc_biggest_side = self::$test_x;
            self::$calc_step = self::$step_x;
            self::$calc_measure = self::MAX_WIDTH;
        } else {
            self::$calc_biggest_side = self::$test_y;
            self::$calc_step = self::$step_x; //не ошибка
            self::$calc_measure = self::MAX_HEIGHT;
        }
        if (($current_amount = self::$calc_biggest_side / self::$calc_step) > self::$calc_measure) {
            self::$reduce_coef = $current_amount / self::$calc_measure;
            self::$new_width = self::$test_x / self::$reduce_coef;
            self::$new_height = self::$test_y / self::$reduce_coef;
        }
    }

    public static function buildFrameset($ires, $x, $y)
    {
        $output = [];
        for ($i_y = 0; $i_y < $y; $i_y += self::$step_y) {
            for ($i_x = 0; $i_x < $x; $i_x += self::$step_x) {
                $averages = [];
                for ($cur_x = $i_x; $cur_x < $i_x + self::$step_x; $cur_x++) {
                    for ($cur_y = $i_y; $cur_y < $i_y + self::$step_y; $cur_y++) {
                        if ($cur_x > $x - 1 || $cur_y > $y - 1) continue;
                        $rgb = imagecolorat($ires, $cur_x, $cur_y);
                        $colors = imagecolorsforindex($ires, $rgb);
                        $average = 0;
                        foreach ($colors as $name => $val) {
                            if ($name != 'alpha') $average += $val;
                        }
                        $average = floor($average / 3);
                        $averages[] = $average;
                    }
                }
                $averages = array_sum($averages) / (sizeof($averages) ?: 1);
                $output[$i_y][$i_x] = $averages;
            }
        }
        return $output;
    }

    public static function formText($frameset)
    {
        self::$length = sizeof(self::$symbols) - 1;
        $strs = [];
        foreach ($frameset as $frame) {
            $str = "";
            foreach ($frame as $index => $y) {
                foreach ($y as $x) {
                    $str .= self::$symbols[intval(round(($x / 255) * self::$length))];
                }
                $str .= "\n";
            }
            $strs[] = $str;
        }
        return $strs;
    }

    public function show($ascii_uuid, Request $request)
    {
        $ascii = Ascii::where('uuid', $ascii_uuid)->firstOrFail();
        return view('pages.ascii')->with('ascii', $ascii);
    }

    public function embed($ascii_uuid, Request $request)
    {
        $ascii = Ascii::where('uuid', $ascii_uuid)->firstOrFail();
        return view('pages.embed')->with('ascii', $ascii);
    }

    public function enlist($page = 1)
    {
        $on_page = 10;
        $items = DB::table('ascii')->offset($on_page * (intval($page) - 1))->limit($on_page)->orderBy('id', 'desc')->get();
        $total = DB::table('ascii')->count();
        return view('pages.allAscii')->with(['items' => $items, 'page' => $page, 'total' => $total, 'on_page' => $on_page]);
    }

    public function destroy($id, Request $request)
    {
        $ascii = Ascii::findOrFail($id);
        self::purge($ascii);
        return redirect()->back()->with('success-message', Lang::get('global.successfully-removed'));
    }

    public function cleanse()
    {
        $asciis = DB::table('ascii')->orderBy('id', 'asc')->limit(100)->get();
        foreach ($asciis as $ascii) {
            self::purge($ascii);
        }
    }

    public static function purge(&$ascii)
    {
        $entities = [];
        if ($ascii->file->sourceFile) $entities[] = './files/stash/' . $ascii->file->sourceFile->name;
        if ($ascii->image) $entities[] = './images/stash/' . $ascii->image->name;
        if ($ascii->image) $entities[] = './images/stash/' . $ascii->image->filename_h100;
        if ($ascii->image) $entities[] = './images/stash/' . $ascii->image->filename_h500;
        if ($ascii->image) $entities[] = './images/stash/' . $ascii->image->filename_ascii;
        if ($ascii->image) $entities[] = './images/stash/' . $ascii->image->filename_ascii_gif;
        foreach ($entities as $path) {
            if (is_file($path) && file_exists($path)) unlink($path);
        }
        if ($ascii->image) $ascii->image->delete();
        if ($ascii->file) $ascii->file->delete();
        $ascii->delete();
    }
}

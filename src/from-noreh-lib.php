<?php

namespace Noreh\System\Libraries;

/**
 * Image Manipulation Class
 * 
 * Created for thumbnail generation.
 * 
 * @author	https://github.com/akinuri
 * @link	https://github.com/akinuri/Image.php
 * @link    https://www.php.net/manual/en/book.image.php
 */
class Image {
    
    
    /**
     * The directory the image resides.
     */
    public $dir = null;
    
    
    /**
     * Path to the image.
     */
    public $path = null;
    
    
    /**
     * Full name of the file.
     */
    public $name = null;
    
    
    /**
     * Image width.
     */
    public $width = null;
    
    
    /**
     * Image height.
     */
    public $height = null;
    
    
    /**
     * Image type.
     */
    public $type = null;
    
    
    /**
     * Image channel count (R, G, B).
     */
    public $channels = null;
    
    
    /**
     * Image bits.
     */
    public $bits = null;
    
    
    /**
     * Image MIME type.
     */
    public $mime = null;
    
    
    /**
     * Image file extension.
     */
    public $ext = null;
    
    
    /**
     * Image file size.
     */
    public $size = null;
    
    
    /**
     * A handle to the image resource.
     */
    public $resource = null;
    
    
    /**
     * Transformations applied to the image.
     */
    public $transformations = [
        // [method, parameters]
    ];
    
    
    
    /**
     * Image types (constants map).
     * https://www.php.net/manual/en/image.constants.php
     */
    public static $IMAGE_TYPES = [
        "1"  => "IMAGETYPE_GIF",
        "2"  => "IMAGETYPE_JPEG",
        "3"  => "IMAGETYPE_PNG",
        "4"  => "IMAGETYPE_SWF",
        "5"  => "IMAGETYPE_PSD",
        "6"  => "IMAGETYPE_BMP",
        "7"  => "IMAGETYPE_TIFF_II",
        "8"  => "IMAGETYPE_TIFF_MM",
        "9"  => "IMAGETYPE_JPEG2000",
        "9"  => "IMAGETYPE_JPC",
        "10" => "IMAGETYPE_JP2",
        "11" => "IMAGETYPE_JPX",
        "12" => "IMAGETYPE_JB2",
        "13" => "IMAGETYPE_SWC",
        "14" => "IMAGETYPE_IFF",
        "15" => "IMAGETYPE_WBMP",
        "16" => "IMAGETYPE_XBM",
        "17" => "IMAGETYPE_ICO",
        "18" => "IMAGETYPE_WEBP",
    ];
    
    
    
    public function __construct(string $file_path = null, bool $load = true) {
        if ($file_path) {
            $this->read($file_path);
            if ($load) {
                $this->load();
            }
        }
    }
    
    
    
    /**
     * Reads image data.
     */
    protected function read(string $file_path) {
        
        // https://www.php.net/manual/en/function.file-exists.php
        // https://www.php.net/manual/en/function.dirname
        // https://www.php.net/manual/en/function.basename.php
        // https://www.php.net/manual/en/function.getimagesize.php
        // https://www.php.net/manual/en/function.filesize.php
        
        if (!file_exists($file_path)) return false;
        
        $this->dir  = dirname($this->path);
        $this->path = $file_path;
        $this->name = basename($file_path);
        
        $size = getimagesize($file_path);
        
        if (!$size) return false;
        
        $this->width    = $size[0];
        $this->height   = $size[1];
        $this->type     = self::$IMAGE_TYPES[$size[2]];
        $this->channels = isset($size["channels"]) ? $size["channels"] : null;
        $this->bits     = $size["bits"];
        $this->mime     = $size["mime"];
        $this->ext      = explode("/", $size["mime"])[1];
        $this->size     = filesize($file_path);
        
        return true;
    }
    
    
    
    /**
     * Creates an image (resource) to manipulate.
     */
    protected function load() {
        
        // https://www.php.net/manual/en/function.imagecreatefromjpeg.php
        
        if (!$this->path || !file_exists($this->path)) {
            return false;
        }
        
        $this->resource = call_user_func("imagecreatefrom" . $this->ext, $this->path);
        
        return true;
    }
    
    
    
    /**
     * Checks whether the image is transparent or not.
     */
    public function isTransparent() : bool {
        
        if (!in_array($this->mime, ["image/png"])) {
            return false;
        }
        
        $image   = $this->resource;
        $image_w = $this->width;
        $image_h = $this->height;
        
        $thumb_size = 50;
        
        if ($this->width > $thumb_size || $this->height > $thumb_size) {
            $image = imagecreatetruecolor($thumb_size, $thumb_size);
            imagealphablending($image, FALSE);
            imagecopyresized($image, $this->resource, 0, 0, 0, 0, $thumb_size, $thumb_size, $this->width, $this->height);
            $image_w = $thumb_size;
            $image_h = $thumb_size;
        }
        
        for ($y = 0; $y < $image_h - 1; $y++) {
            for ($x = 0; $x < $image_w - 1; $x++) {
                $color_index = imagecolorat($image, $x, $y);
                $color_array = imagecolorsforindex($image, $color_index);
                if ($color_array["alpha"] != 0) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    
    
    /**
     * Converts an image between different formats.
     */
    public function convert($to = "jpeg") {
        
        if (!$this->resource) return false;
        
        $this->transformations[] = [__FUNCTION__, get_defined_vars()];
        
        switch ($to) {
            case "jpg":
            case "jpeg":
                $jpeg = imagecreatetruecolor($this->width, $this->height);
                imagefill($jpeg, 0, 0, imagecolorallocate($jpeg, 255, 255, 255));
                imagealphablending($jpeg, TRUE);
                imagecopy($jpeg, $this->resource, 0, 0, 0, 0, $this->width, $this->height);
                imagedestroy($this->resource);
                $this->resource = $jpeg;
                $this->mime = "image/jpeg";
                $this->ext  = "jpeg";
                $this->size = null;
                break;
        }
        
        return true;
    }
    
    
    
    /**
     * Crops an image.
     */
    public function crop(int $x = null, int $y = null, int $width = null, int $height = null) {
        
        if (!$this->resource) return false;
        
        if ($x === null || $y === null || !$width || !$height) return false;
        
        if ( !(0 <= $x && $x <= $this->width) ) return false;
        if ( !(0 <= $y && $y <= $this->height) ) return false;
        
        $this->transformations[] = [__FUNCTION__, get_defined_vars()];
        
        $right_offset  = $this->width  - ($x + $width);
        $bottom_offset = $this->height - ($y + $height);
        if ($right_offset < 0)  $width  += $right_offset;
        if ($bottom_offset < 0) $height += $bottom_offset;
        
        $cropped = imagecrop($this->resource, [
            "x"      => $x,
            "y"      => $y,
            "width"  => $width,
            "height" => $height,
        ]);
        
        if (!$cropped) return false;
        
        $this->resource = $cropped;
        $this->width    = $width;
        $this->height   = $height;
        $this->size     = null;
        
        return true;
    }
    
    
    
    /**
     * Crops an image into a square shape.
     */
    public function square() {
        
        $this->transformations[] = [__FUNCTION__, get_defined_vars()];
        
        $short_side = null;
        $long_side  = null;
        
        if ($this->width > $this->height) {
            $long_side  = "width";
            $short_side = "height";
        } else {
            $long_side  = "height";
            $short_side = "width";
        }
        
        $size = $this->{$short_side};
        
        $offset         = $this->{$long_side} - $this->{$short_side};
        $center_offset  = floor($offset / 2);
        
        $crop_area = [
            "x"      => null,
            "y"      => null,
            "width"  => $size,
            "height" => $size,
        ];
        
        if ($long_side == "width") {
            $crop_area["x"] = $center_offset;
            $crop_area["y"] = 0;
        } else {
            $crop_area["y"] = $center_offset;
            $crop_area["x"] = 0;
        }
        
        return call_user_func_array([$this, "crop"], $crop_area);
    }
    
    
    
    /**
     * Resizes an image to specificied dimensions.
     */
    public function resize(int $width, int $height) {
        
        if (!$this->resource) return false;
        
        $this->transformations[] = [__FUNCTION__, get_defined_vars()];
        
        $resized = imagecreatetruecolor($width, $height);
        imagealphablending($resized, false);
        imagecopyresampled($resized, $this->resource, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        imagedestroy($this->resource);
        $this->resource = $resized;
        $this->width = $width;
        $this->height = $height;
        $this->size  = null;
        
        return true;
    }
    
    
    
    /**
     * Resizes an image so that it fits in an area.
     */
    public function fit(int $box_width, int $box_height) {
        
        $this->transformations[] = [__FUNCTION__, get_defined_vars()];
        
        $new_width  = 0;
        $new_height = 0;
        
        if ($box_width * $this->height < $box_height * $this->width) {
            $new_width  = $box_width;
            $new_height = $this->height * ($box_width / $this->width);
        } else {
            $new_height = $box_height;
            $new_width  = $this->width * ($box_height / $this->height);
        }
        
        return $this->resize($new_width, $new_height);
    }
    
    
    
    /**
     * Resizes an image so that it fills in an area.
     */
    public function fill(int $box_width, int $box_height) {
        
        $this->transformations[] = [__FUNCTION__, get_defined_vars()];
        
        $new_width  = 0;
        $new_height = 0;
        
        if ($box_width * $this->height > $box_height * $this->width) {
            $new_width  = $box_width;
            $new_height = $this->height * $box_width / $this->width;
        } else {
            $new_width  = $this->width * $box_height / $this->height;
            $new_height = $box_height;
        }
        
        $result[] = $this->resize($new_width, $new_height);
        
        $changed = false;
        
        if ($new_width > $box_width) {
            $new_width = $box_width;
            $changed = true;
        }
        if ($new_height > $box_height) {
            $new_height = $box_height;
            $changed = true;
        }
        
        if ($changed) {
            $result[] = $this->crop(0, 0, $new_width, $new_height);
        }
        
        return !in_array(false, $result, true);
    }
    
    
    
    /**
     * Outputs the image to browser.
     */
    public function show() {
        if (!$this->resource) return false;
        header("Content-Type: " . $this->mime);
        switch ($this->ext) {
            case "jpeg":
                imagejpeg($this->resource);
                break;
            case "png":
                imagesavealpha($this->resource, true);
                imagepng($this->resource);
                break;
        }
    }
    
    
    
    /**
     * Creates an image at the specified location.
     */
    public function save(string $file_path, int $quality = -1) {
        
        if (!$this->resource) return false;
        
        $path_parts = explode(".", $file_path);
        $file_ext   = array_pop($path_parts);
        
        switch ($file_ext) {
            case "jpg":
            case "jpeg":
                return imagejpeg($this->resource, $file_path, $quality);
                break;
            case "png":
                if ($this->isTransparent()) {
                    imagealphablending($this->resource, true);
                    imagesavealpha($this->resource, true);
                }
                return imagepng($this->resource, $file_path, $quality);
                break;
        }
        
        return false;
    }
    
    
    
    /**
     * Destroys image, resets handle and all other properties.
     */
    public function destroy() {
        if ($this->resource) {
            imagedestroy($this->resource);
            $this->resource = null;
            $this->dir  = null;
            $this->path = null;
            $this->name = null;
        }
        $this->width    = null;
        $this->height   = null;
        $this->channels = null;
        $this->bits     = null;
        $this->mime     = null;
        $this->ext      = null;
        $this->size     = null;
    }
    
    
    
}

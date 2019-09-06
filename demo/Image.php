<?php

function num_in_range($int = null, $min = null, $max = null) {
    if (in_array(null, [$int, $min, $max], true)) return false;
    return $min <= $int && $int <= $max;
}

class Image {
    
    public $resource = null;
    
    public $width    = null;
    public $height   = null;
    public $channels = null;
    public $bits     = null;
    public $mime     = null;
    public $ext      = null;
    public $size     = null;
    
    
    function __construct($file_path = null) {
        $this->read($file_path);
    }
    
    
    
    function read($file_path = null) {
        
        if (!$file_path) return false;
        if (!file_exists($file_path)) return false;
        
        $size = getimagesize($file_path);
        
        if (!$size) return false;
        
        $this->width    = $size[0];
        $this->height   = $size[1];
        $this->channels = $size[2];
        $this->bits     = $size["bits"];
        $this->mime     = $size["mime"];
        $this->ext      = explode("/", $size["mime"])[1];
        $this->size     = filesize($file_path);
        
        $this->resource = call_user_func("imagecreatefrom" . $this->ext, $file_path);
        
        return true;
    }
    
    
    
    function convert($to = "jpeg") {
        
        if (!$this->resource) return false;
        
        $jpeg = imagecreatetruecolor($this->width, $this->height);
        imagefill($jpeg, 0, 0, imagecolorallocate($jpeg, 255, 255, 255));
        imagealphablending($jpeg, TRUE);
        imagecopy($jpeg, $this->resource, 0, 0, 0, 0, $this->width, $this->height);
        imagedestroy($this->resource);
        $this->resource = $jpeg;
        
        $this->mime = "image/jpeg";
        $this->ext  = "jpeg";
        $this->size = null;
        
        return true;
    }
    
    
    
    function square() {
        
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
    
    
    
    function crop($x = null, $y = null, $width = null, $height = null) {
        
        if (!$this->resource) return false;
        
        if ($x === null || $y === null || !$width || !$height) return false;
        
        if (!num_in_range($x, 0, $this->width)) return false;
        if (!num_in_range($y, 0, $this->height)) return false;
        
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
    
    
    
    function resize($width = null, $height = null) {
        
        if (!$this->resource) return false;
        
        if (!$width || !$height) return false;
        
        $resized = imagecreatetruecolor($width, $height);
        imagealphablending($resized, false);
        imagecopyresampled($resized, $this->resource, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        imagedestroy($this->resource);
        $this->resource = $resized;
        $this->width = $width;
        $this->width = $height;
        $this->size  = null;
        
        return true;
    }
    
    
    
    function show() {
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
    
    
    
    function save($file_path = null, $quality = -1) {
        
        if (!$this->resource) return false;
        if (!$file_path) return false;
        
        $path_parts = explode(".", $file_path);
        $file_ext   = array_pop($path_parts);
        
        switch ($file_ext) {
            case "jpg":
            case "jpeg":
                return imagejpeg($this->resource, $file_path, $quality);
                break;
            case "png":
                return imagepng($this->resource, $file_path, $quality);
                break;
        }
        
        return false;
    }
    
    
    
    function destroy() {
        if ($this->resource) {
            imagedestroy($this->resource);
            $this->resource = null;
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

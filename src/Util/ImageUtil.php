<?php

namespace JstnThms\FileBundle\Util;

class ImageUtil
{
    public static function getExtension($filepath)
    {
        return strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    }
    
    public static function getImageResource($filepath, $ext = null)
    {
        if (null === $ext) {
            $ext = self::getExtension($filepath);
        }
      
        if ('jpg' === $ext || 'jpeg' === $ext) {
            return imagecreatefromjpeg($filepath);
        }
        else if ('png' === $ext) {
            return imagecreatefrompng($filepath);
        }
        else if ('gif' === $ext) {
            return imagecreatefromgif($filepath);
        }
        else {
            return null;
        }
    }
    
    public static function doFlipRotate($im, $filepath)
    {
        $exif = @exif_read_data($filepath);

        if(!empty($exif['Orientation'])) {
            $orient = $exif['Orientation'];
          
            if (2 === $orient) {
                // horizontal flip
                imageflip($im, 1);
            }
            else if (3 === $orient) {
                // 180 rotate left
                $im = imagerotate($im, 180, 0);
            }
            else if (4 === $orient) {
                // vertical flip
                imageflip($im, 2);
            }
            else if (5 === $orient) {
                // vertical flip + 90 rotate right
                imageflip($im, 2);
                $im = imagerotate($im, -90, 0);
            }
            else if (6 === $orient) {
                // 90 rotate right
                $im = imagerotate($im, -90, 0);
            }
            else if (7 === $orient) {
                // horizontal flip + 90 rotate right
                imageflip($im, 1);
                $im = imagerotate($im, -90, 0);
            }
            else if (8 === $orient) {
                // 90 rotate left
                $im = imagerotate($im, 90, 0);
            }
        }
      
        return $im;
    }
    
    public static function saveImage($im, $filepath, $ext = null)
    {
        if (null === $ext) {
            $ext = self::getExtension($filepath);
        }
      
        imagesavealpha($im, true);
      
        if ('jpg' === $ext || 'jpeg' === $ext) {
            imagejpeg($im, $filepath, 100);
        }
        else if ('png' === $ext) {
            imagepng($im, $filepath, 9);
        }
        else if ('gif' === $ext) {
            imagegif($im, $filepath);
        }
    }
  
    public static function resizeAndCropImage($im, $imW, $imH, $resizeW = null, $resizeH = null)
    {
        if (null === $resizeW && null === $resizeH) {
            return $im;
        }
        
        if (null === $resizeW) {
            $resizeW = ($imW / $imH) * $resizeH;
        }
        else if (null === $resizeH) {
            $resizeH = ($imH / $imW) * $resizeW;
        }
        
        $im_ratio = $imW / $imH;
        $resize_ratio = $resizeW / $resizeH;
        
        $srcX = $srcY = $srcW = $srcH = 0;
        
        if ($im_ratio >= $resize_ratio) {
            $srcY = 0;
            $srcH = $imH;
            $srcW = floor($srcH * $resize_ratio);
            $srcX = floor(($imW - $srcW) / 2);
        }
        else {
            $srcX = 0;
            $srcW = $imW;
            $srcH = floor($srcW / $resize_ratio);
            $srcY = floor(($imH - $srcH) / 2);
        }
        
        $resize = imagecreatetruecolor($resizeW, $resizeH);
        imagefill($resize, 0, 0, imagecolorallocatealpha($resize, 0, 0, 0, 127));
        
        imagealphablending($resize, false);
        imagecopyresampled($resize, $im, 0, 0, $srcX, $srcY, $resizeW, $resizeH, $srcW, $srcH);
        
        return $resize;
    }
}

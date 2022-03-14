<?php

namespace OHMedia\FileBundle\Util;

use GDImage;

class ImageResource
{
    private $im;
    private $width;
    private $height;
    private $filepath;

    private function __construct(GDImage $im, string $filepath)
    {
        $this->im = $im;

        $this->width = imagesx($this->im);
        $this->height = imagesy($this->im);

        $this->filepath = $filepath;
    }

    public static function create(string $filepath): ?self
    {
        $ext = ImageUtil::getExtension($filepath);

        $im = null;

        if ('jpg' === $ext || 'jpeg' === $ext) {
            $im = imagecreatefromjpeg($filepath);
        }
        else if ('png' === $ext) {
            $im = imagecreatefrompng($filepath);
        }
        else if ('gif' === $ext) {
            $im = imagecreatefromgif($filepath);
        }

        if ($im instanceof GDImage) {
            return new static($im, $filepath);
        }

        return null;
    }

    public function fixOrientation(): self
    {
        $exif = @exif_read_data($this->filepath);

        if(!empty($exif['Orientation'])) {
            $orient = $exif['Orientation'];

            if (2 === $orient) {
                // horizontal flip
                imageflip($this->im, 1);
            }
            else if (3 === $orient) {
                // 180 rotate left
                $this->im = imagerotate($this->im, 180, 0);
            }
            else if (4 === $orient) {
                // vertical flip
                imageflip($this->im, 2);
            }
            else if (5 === $orient) {
                // vertical flip + 90 rotate right
                imageflip($this->im, 2);
                $this->im = imagerotate($this->im, -90, 0);
            }
            else if (6 === $orient) {
                // 90 rotate right
                $this->im = imagerotate($this->im, -90, 0);
            }
            else if (7 === $orient) {
                // horizontal flip + 90 rotate right
                imageflip($this->im, 1);
                $this->im = imagerotate($this->im, -90, 0);
            }
            else if (8 === $orient) {
                // 90 rotate left
                $this->im = imagerotate($this->im, 90, 0);
            }
        }

        return $this;
    }

    public function resize(?int $resizeW, ?int $resizeH): self
    {
        if (null === $resizeW && null === $resizeH) {
            return $this;
        }

        if (null === $resizeW) {
            $resizeW = ImageUtil::getTargetWidth(
                $this->width,
                $this->height,
                $resizeH
            );
        }
        else if (null === $resizeH) {
            $resizeH = ImageUtil::getTargetHeight(
                $this->width,
                $this->height,
                $resizeW
            );
        }

        $imRatio = $this->width / $this->height;
        $resizeRatio = $resizeW / $resizeH;

        $srcX = $srcY = $srcW = $srcH = 0;

        if ($imRatio >= $resizeRatio) {
            $srcY = 0;
            $srcH = $this->height;
            $srcW = floor($srcH * $resizeRatio);
            $srcX = floor(($this->width - $srcW) / 2);
        }
        else {
            $srcX = 0;
            $srcW = $this->width;
            $srcH = floor($srcW / $resizeRatio);
            $srcY = floor(($this->height - $srcH) / 2);
        }

        $old = $this->im;

        $this->im = imagecreatetruecolor($resizeW, $resizeH);
        imagefill($this->im, 0, 0, imagecolorallocatealpha($this->im, 0, 0, 0, 127));

        imagealphablending($this->im, false);
        imagecopyresampled($this->im, $old, 0, 0, $srcX, $srcY, $resizeW, $resizeH, $srcW, $srcH);

        return $this;
    }

    public function save(string $filepath = null): bool
    {
        if (null === $filepath) {
            $filepath = $this->filepath;
        }

        $ext = ImageUtil::getExtension($filepath);

        imagesavealpha($this->im, true);

        if ('jpg' === $ext || 'jpeg' === $ext) {
            return imagejpeg($this->im, $filepath, 100);
        }
        else if ('png' === $ext) {
            return imagepng($this->im, $filepath, 9);
        }
        else if ('gif' === $ext) {
            return imagegif($this->im, $filepath);
        }

        return false;
    }
}

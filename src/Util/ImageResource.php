<?php

namespace OHMedia\FileBundle\Util;

use GDImage;

class ImageResource
{
    const TYPE_IMAGICK = 'imagick';
    const TYPE_GD = 'gd';

    private $im;
    private $width;
    private $height;
    private $filepath;

    private function __construct($im, string $filepath)
    {
        $this->im = $im;

        $this->setDimensions();

        $this->filepath = $filepath;
    }

    private function setDimensions()
    {
        if ($this->im instanceof GDImage) {
            $this->setDimensionsGd();
        }
        else {
            $this->setDimensionsImagick();
        }
    }

    private function setDimensionsGd()
    {
        $this->width = imagesx($this->im);
        $this->height = imagesy($this->im);
    }

    private function setDimensionsImagick()
    {
        $this->width = $this->im->getImageWidth();
        $this->height = $this->im->getImageHeight();
    }

    public static function create(string $filepath): ?self
    {
        if (class_exists('Imagick')) {
            return static::createImagick($filepath);
        }
        else {
            return static::createGd($filepath);
        }
    }

    private static function createImagick(string $filepath): ?self
    {
        try {
            $im = new \Imagick($filepath);

            return new static($im, $filepath);
        } catch(\ImagickException $error) {
            return null;
        }
    }

    private static function createGd(string $filepath): self
    {
        $ext = FileUtil::getExtension($filepath);

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
        if ($this->im instanceof GDImage) {
            $this->fixOrientationGd();
        }
        else {
            $this->fixOrientationImagick();
        }

        return $this;
    }

    private function fixOrientationGd(): void
    {
        $exif = @exif_read_data($this->filepath);

        if(empty($exif['Orientation'])) {
            return $this;
        }

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

    private function fixOrientationImagick(): void
    {
        $orient = $this->im->getImageOrientation();

        if (empty($orient)) {
            return $this;
        }

        $transparent = new \ImagickPixel('transparent');

        if (\Imagick::ORIENTATION_TOPRIGHT === $orient) {
            $this->im->flipImage();
            $this->im->rotateImage($transparent, 180);
        }
        else if (\Imagick::ORIENTATION_BOTTOMRIGHT === $orient) {
            $this->im->rotateImage($transparent, 180);
        }
        else if (\Imagick::ORIENTATION_BOTTOMLEFT === $orient) {
            $this->im->flipImage();
        }
        else if (\Imagick::ORIENTATION_LEFTTOP === $orient) {
            $this->im->rotateImage($transparent, -90);
            $this->im->flipImage();
        }
        else if (\Imagick::ORIENTATION_RIGHTTOP === $orient) {
            $this->im->rotateImage($transparent, 90);
        }
        else if (\Imagick::ORIENTATION_RIGHTBOTTOM === $orient) {
            $this->im->rotateImage($transparent, 90);
            $this->im->flipImage();
        }
        else if (Imagick::ORIENTATION_LEFTBOTTOM === $orient) {
            $this->im->rotateImage($transparent, -90);
        }

        $this->im->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
    }

    public function resize(?int $resizeW, ?int $resizeH): self
    {
        if (null === $resizeW && null === $resizeH) {
            return $this;
        }

        if (null === $resizeW) {
            $resizeW = FileUtil::getTargetWidth(
                $this->width,
                $this->height,
                $resizeH
            );
        }
        else if (null === $resizeH) {
            $resizeH = FileUtil::getTargetHeight(
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

        if ($this->im instanceof GDImage) {
            $this->resizeGd($resizeW, $resizeH, $srcX, $srcY, $srcW, $srcH);
        }
        else {
            $this->resizeImagick($resizeW, $resizeH, $srcX, $srcY, $srcW, $srcH);
        }

        return $this;
    }

    private function resizeGd(
        int $resizeW, int $resizeH,
        int $srcX, int $srxY,
        int $srcW, int $srxH
    ): void
    {
        $old = $this->im;

        $this->im = imagecreatetruecolor($resizeW, $resizeH);
        imagefill($this->im, 0, 0, imagecolorallocatealpha($this->im, 0, 0, 0, 127));

        imagealphablending($this->im, false);
        imagecopyresampled($this->im, $old, 0, 0, $srcX, $srcY, $resizeW, $resizeH, $srcW, $srcH);

    }

    private function resizeImagick(
        int $resizeW, int $resizeH,
        int $srcX, int $srxY,
        int $srcW, int $srxH
    ): void
    {
        $this->im->cropImage($srcW, $srcH, $srcX, $srcY);
        $this->im->resize($resizeW, $resizeH, \Imagick::FILTER_SINC, 1);
    }

    public function save(string $filepath = null): bool
    {
        if (null === $filepath) {
            $filepath = $this->filepath;
        }

        if ($this->im instanceof GDImage) {
            return $this->saveGd($filepath);
        }
        else {
            return $this->saveImagick($filepath);
        }
    }

    private function saveGd(string $filepath): bool
    {
        $ext = FileUtil::getExtension($filepath);

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

    private function saveImagick(string $filepath): bool
    {
        return $this->im->writeImage($filepath);
    }
}

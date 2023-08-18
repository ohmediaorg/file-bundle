<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Entity\ImageResize;
use OHMedia\FileBundle\Repository\ImageResizeRepository;
use OHMedia\FileBundle\Util\FileUtil;
use OHMedia\FileBundle\Util\MimeTypeUtil;

class ImageManager
{
    private $fileManager;
    private $imageResizeRepository;

    public function __construct(
        FileManager $fileManager,
        ImageResizeRepository $imageResizeRepository
    ) {
        $this->fileManager = $fileManager;
        $this->imageResizeRepository = $imageResizeRepository;
    }

    public function getImagePath(Image $image, int $width = null, int $height = null)
    {
        $resize = $this->getImageResize($image, $width, $height);

        $file = $resize
            ? $resize->getFile()
            : $image->getFile();

        return $file ? $this->fileManager->getWebPath($file) : '';
    }

    public function render(Image $image, array $attributes = [])
    {
        $this->setImageTagAttributes($image, $attributes);

        $attributesString = [];
        foreach ($attributes as $attribute => $value) {
            $attributesString[] = sprintf(
                '%s="%s"',
                $attribute,
                htmlspecialchars($value)
            );
        }

        $attributesString = implode(' ', $attributesString);

        return "<img $attributesString />";
    }

    private function setImageTagAttributes(Image $image, array &$attributes): void
    {
        $attributes['alt'] = $image->getAlt();

        $file = $image->getFile();

        if (!$file) {
            $attributes['src'] = '';

            return;
        }

        if (MimeTypeUtil::SVG === $image->getFile()->getMimeType()) {
            $attributes['src'] = $this->fileManager->getWebPath($file);

            return;
        }

        $width = !empty($attributes['width']) ? $attributes['width'] : null;
        $height = !empty($attributes['height']) ? $attributes['height'] : null;

        $resize = $this->getImageResize($image, $width, $height);

        if ($resize) {
            $file = $resize->getFile();

            $attributes['width'] = $resize->getWidth();
            $attributes['height'] = $resize->getHeight();
        } else {
            $attributes['width'] = $image->getWidth();
            $attributes['height'] = $image->getHeight();
        }

        $attributes['src'] = $this->fileManager->getWebPath($file);
    }

    private function getImageResize(
        Image $image,
        int $width = null,
        int $height = null
    ): ?ImageResize {
        if (null === $width && null === $height) {
            return null;
        }

        $origWidth = $image->getWidth();
        $origHeight = $image->getHeight();

        if (!$origWidth || !$origHeight) {
            // something is not right, don't try to resize
            return null;
        }

        if (null === $width) {
            $width = FileUtil::getTargetWidth(
                $origWidth,
                $origHeight,
                $height
            );
        } elseif (null === $height) {
            $height = FileUtil::getTargetHeight(
                $origWidth,
                $origHeight,
                $width
            );
        }

        $name = sprintf('%sx%s', $width, $height);

        $resize = $image->getResize($name);

        if (!$resize) {
            $copy = $this->fileManager->copy($image->getFile());

            $copy
                ->setName($copy->getName().'-'.$name)
                ->setBrowser(false)
            ;

            $resize = new ImageResize();
            $resize
                ->setFile($copy)
                ->setName($name)
                ->setWidth($width)
                ->setHeight($height)
                ->setImage($image);

            $this->imageResizeRepository->save($resize, true);
        }

        return $resize;
    }
}

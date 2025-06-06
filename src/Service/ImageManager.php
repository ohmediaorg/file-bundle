<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Util\FileUtil;
use OHMedia\FileBundle\Util\MimeTypeUtil;

class ImageManager
{
    public function __construct(
        private FileManager $fileManager,
        private FileRepository $fileRepository
    ) {
    }

    public function getImagePath(File $file, ?int $width = null, ?int $height = null)
    {
        $resize = $this->getResize($file, $width, $height);

        if ($resize) {
            return $this->fileManager->getWebPath($resize);
        }

        return $this->fileManager->getWebPath($file);
    }

    public function render(File $file, array $attributes = [])
    {
        $this->setImageTagAttributes($file, $attributes);

        $attributesString = [];
        foreach ($attributes as $attribute => $value) {
            $attributesString[] = sprintf(
                '%s="%s"',
                $attribute,
                htmlspecialchars($value ?? '')
            );
        }

        $attributesString = implode(' ', $attributesString);

        return "<img $attributesString />";
    }

    private function setImageTagAttributes(File $file, array &$attributes): void
    {
        if (!isset($attributes['loading'])) {
            $attributes['loading'] = 'lazy';
        }

        $attributes['alt'] = $file->getAlt();

        if (!$file) {
            $attributes['src'] = '';

            return;
        }

        $mimeType = $file->getMimeType();

        if ($mimeType && !MimeTypeUtil::isResizeEligible($mimeType)) {
            $attributes['src'] = $this->fileManager->getWebPath($file);

            return;
        }

        $width = !empty($attributes['width']) ? $attributes['width'] : null;
        $height = !empty($attributes['height']) ? $attributes['height'] : null;

        $resize = $this->getResize($file, $width, $height);

        if ($resize) {
            $attributes['width'] = $resize->getWidth();
            $attributes['height'] = $resize->getHeight();
            $attributes['src'] = $this->fileManager->getWebPath($resize);
        } else {
            $attributes['width'] = $file->getWidth();
            $attributes['height'] = $file->getHeight();
            $attributes['src'] = $this->fileManager->getWebPath($file);
        }
    }

    private function getResize(
        File $file,
        ?int $width = null,
        ?int $height = null
    ): ?File {
        if (null === $width && null === $height) {
            return null;
        }

        $origWidth = $file->getWidth();
        $origHeight = $file->getHeight();

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

        $resize = $file->getResize($width, $height);

        if (!$resize) {
            $resize = $this->fileManager->copy($file);

            $name = sprintf('%sx%s', $width, $height);

            $resize
                ->setName($resize->getName().'-'.$name)
                ->setBrowser(false)
                ->setImage(true)
                ->setAlt($file->getAlt())
                ->setWidth($width)
                ->setHeight($height)
                ->setResizeParent($file)
            ;

            $this->fileRepository->save($resize, true);
        }

        return $resize;
    }
}

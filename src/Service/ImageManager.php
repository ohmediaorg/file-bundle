<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Util\FileUtil;
use OHMedia\FileBundle\Util\MimeTypeUtil;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ImageManager
{
    public function __construct(
        private FileManager $fileManager,
        private FileRepository $fileRepository,
        #[Autowire('%oh_media_file.file_browser.max_image_dimension%')]
        private int $maxImageDimension
    ) {
    }

    public function constrainWidthAndHeight(
        File $image,
        ?int $width,
        ?int $height
    ): array {
        // not dealing with 0 or negatives
        if ($width < 1) {
            $width = null;
        }

        if ($height < 1) {
            $height = null;
        }

        if ($width && $height) {
            $ratio = $width / $height;

            if ($ratio > 1) {
                // width is larger
                $width = min($width, $this->maxImageDimension);
                $height = $width / $ratio;
            } else {
                // height is larger or equal
                $height = min($height, $this->maxImageDimension);
                $width = $height * $ratio;
            }
        } elseif ($width) {
            $width = min($width, $this->maxImageDimension);
        } elseif ($height) {
            $height = min($height, $this->maxImageDimension);
        } else {
            $width = min($image->getWidth(), $this->maxImageDimension);
        }

        return [$width, $height];
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

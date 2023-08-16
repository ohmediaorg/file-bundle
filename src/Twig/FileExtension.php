<?php

namespace OHMedia\FileBundle\Twig;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Entity\ImageResize;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\FileBundle\Util\FileUtil;
use OHMedia\FileBundle\Util\MimeTypeUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_file_entity', [$this, 'isFileEntity']),
            new TwigFunction('is_file_folder_entity', [$this, 'isFileFolderEntity']),
            new TwigFunction('is_image_entity', [$this, 'isImageEntity']),
            new TwigFunction('is_image_resize_entity', [$this, 'isImageResizeEntity']),
            new TwigFunction('file_size', [$this, 'getFileSize']),
            new TwigFunction('file_size_binary', [$this, 'getFileSizeBinary']),
            new TwigFunction('file_path', [$this, 'getFilePath']),
            new TwigFunction('image_path', [$this, 'getImagePath']),
            new TwigFunction('image_tag', [$this, 'getImageTag'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function isFileEntity(mixed $value): bool
    {
        return $value instanceof File;
    }

    public function isFileFolderEntity(mixed $value): bool
    {
        return $value instanceof FileFolder;
    }

    public function isImageEntity(mixed $value): bool
    {
        return $value instanceof Image;
    }

    public function isImageResizeEntity(mixed $value): bool
    {
        return $value instanceof ImageResize;
    }

    public function getFileSize(File $file, int $precision = 1): string
    {
        return FileUtil::formatBytesDecimal($file->getSize(), $precision);
    }

    public function getFileSizeBinary(File $file, int $precision = 1): string
    {
        return FileUtil::formatBytesBinary($file->getSize(), $precision);
    }

    public function getFilePath(File $file): ?string
    {
        return $this->fileManager->getWebPath($file);
    }

    public function getImagePath(Image $image, int $width = null, int $height = null)
    {
        $resize = $this->fileManager->getImageResize($image, $width, $height);

        $file = $resize
            ? $resize->getFile()
            : $image->getFile();

        return $file ? $this->getFilePath($file) : '';
    }

    public function getImageTag(Image $image, array $attributes = [])
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
            $attributes['src'] = $this->getFilePath($file);

            return;
        }

        $width = !empty($attributes['width']) ? $attributes['width'] : null;
        $height = !empty($attributes['height']) ? $attributes['height'] : null;

        $resize = $this->fileManager->getImageResize($image, $width, $height);

        if ($resize) {
            $file = $resize->getFile();

            $attributes['width'] = $resize->getWidth();
            $attributes['height'] = $resize->getHeight();
        } else {
            $attributes['width'] = $image->getWidth();
            $attributes['height'] = $image->getHeight();
        }

        $attributes['src'] = $this->getFilePath($file);
    }
}

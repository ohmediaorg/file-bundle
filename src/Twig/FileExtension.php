<?php

namespace OHMedia\FileBundle\Twig;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Entity\ImageResize;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\FileBundle\Util\FileUtil;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
    private $manager;

    public function __construct(FileManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_size', [$this, 'getFilesize']),
            new TwigFunction('file_path', [$this, 'getFilePath']),
            new TwigFunction('image_path', [$this, 'getImagePath']),
            new TwigFunction('image_tag', [$this, 'getImageTag'], [
                'is_safe' => ['html']
            ])
        ];
    }

    public function getFilesize(File $file, int $precision = 1): string
    {
        return FileUtil::formatBytes($file->getSize(), $precision);
    }

    public function getFilePath(File $file)
    {
        return $this->manager->getWebPath($file);
    }

    public function getImagePath(Image $image, int $width = null, int $height = null)
    {
        $resize = $this->manager->getImageResize($image, $width, $height);

        $file = $resize
            ? $resize->getFile()
            : $image->getFile();

        return $this->getFile($file);
    }

    public function getImageTag(Image $image, array $attributes = [])
    {
        $width = !empty($attributes['width']) ? $attributes['width'] : null;
        $height = !empty($attributes['height']) ? $attributes['height'] : null;

        $resize = $this->manager->getImageResize($image, $width, $height);

        if ($resize) {
            $file = $resize->getFile();

            $attributes['width'] = $resize->getWidth();
            $attributes['height'] = $resize->getHeight();
        }
        else {
            $file = $image->getFile();

            $attributes['width'] = $image->getWidth();
            $attributes['height'] = $image->getHeight();
        }

        $attributes['src'] = $this->getFile($file);

        $attributes['alt'] = $image->getAlt();

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
}

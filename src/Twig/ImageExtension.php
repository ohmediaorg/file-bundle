<?php

namespace OHMedia\FileBundle\Twig;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Service\ImageManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    private $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('image_path', [$this, 'getImagePath']),
            new TwigFunction('image_tag', [$this, 'getImageTag'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function getImagePath(File $image, int $width = null, int $height = null)
    {
        return $this->imageManager->getImagePath($image, $width, $height);
    }

    public function getImageTag(File $image, array $attributes = [])
    {
        return $this->imageManager->render($image, $attributes);
    }
}

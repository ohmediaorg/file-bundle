<?php

namespace OHMedia\FileBundle\Twig;

use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Entity\ImageResize;
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
            new TwigFunction('is_image_entity', [$this, 'isImageEntity']),
            new TwigFunction('is_image_resize_entity', [$this, 'isImageResizeEntity']),
            new TwigFunction('image_path', [$this, 'getImagePath']),
            new TwigFunction('image_tag', [$this, 'getImageTag'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function isImageEntity(mixed $value): bool
    {
        return $value instanceof Image;
    }

    public function isImageResizeEntity(mixed $value): bool
    {
        return $value instanceof ImageResize;
    }

    public function getImagePath(Image $image, int $width = null, int $height = null)
    {
        return $this->imageManager->getImagePath($image, $width, $height);
    }

    public function getImageTag(Image $image, array $attributes = [])
    {
        return $this->imageManager->render($image, $attributes);
    }
}

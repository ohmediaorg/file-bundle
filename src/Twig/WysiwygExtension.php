<?php

namespace OHMedia\FileBundle\Twig;

use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\FileBundle\Service\ImageManager;
use OHMedia\WysiwygBundle\Twig\AbstractWysiwygExtension;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\TwigFunction;

class WysiwygExtension extends AbstractWysiwygExtension
{
    public function __construct(
        private FileManager $fileManager,
        private FileRepository $fileRepository,
        private ImageManager $imageManager,
        #[Autowire('%oh_media_file.file_browser.max_image_dimension%')]
        private int $maxImageDimension
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_href', [$this, 'fileHref']),
            new TwigFunction('image', [$this, 'image'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function fileHref(int $id)
    {
        $file = $id ? $this->fileRepository->findOneBy([
            'id' => $id,
            'browser' => true,
        ]) : null;

        return $file ? $this->fileManager->getWebPath($file) : '';
    }

    public function image(int $id, ?int $width = null, ?int $height = null)
    {
        $image = $id ? $this->fileRepository->findOneBy([
            'id' => $id,
            'image' => true,
        ]) : null;

        if (!$image || !$image->isBrowser()) {
            return '';
        }

        $attributes = [];

        if ($width && $height) {
            $ratio = $width / $height;

            if ($ratio > 1) {
                $attributes['width'] = min($width, $this->maxImageDimension);
                $attributes['height'] = $attributes['width'] / $ratio;
            } else {
                $attributes['height'] = min($height, $this->maxImageDimension);
                $attributes['width'] = $attributes['height'] * $ratio;
            }
        } elseif ($width) {
            $attributes['width'] = min($width, $this->maxImageDimension);
        } elseif ($height) {
            $attributes['height'] = min($height, $this->maxImageDimension);
        } else {
            $attributes['width'] = min($image->getWidth(), $this->maxImageDimension);
        }

        return $this->imageManager->render($image, $attributes);
    }
}

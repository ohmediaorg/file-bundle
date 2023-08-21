<?php

namespace OHMedia\FileBundle\Twig;

use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Repository\ImageRepository;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\FileBundle\Service\ImageManager;
use OHMedia\WysiwygBundle\Twig\AbstractWysiwygExtension;
use Twig\TwigFunction;

class WysiwygExtension extends AbstractWysiwygExtension
{
    private $fileManager;
    private $fileRepository;
    private $imageManager;
    private $imageRepository;

    public function __construct(
        FileManager $fileManager,
        FileRepository $fileRepository,
        ImageManager $imageManager,
        ImageRepository $imageRepository
    ) {
        $this->fileManager = $fileManager;
        $this->fileRepository = $fileRepository;
        $this->imageManager = $imageManager;
        $this->imageRepository = $imageRepository;
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
        $file = $this->fileRepository->findOneBy([
            'id' => $id,
            'browser' => true,
        ]);

        return $file ? $this->fileManager->getWebPath($file) : '';
    }

    public function image(int $id, int $width = null)
    {
        $image = $this->imageRepository->find($id);

        if (!$image || !$image->isBrowser()) {
            return '';
        }

        $attributes = [];

        if (null !== $width) {
            $attributes['width'] = $width;
        }

        return $this->imageManager->render($image, $attributes);
    }
}

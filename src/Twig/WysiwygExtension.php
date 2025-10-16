<?php

namespace OHMedia\FileBundle\Twig;

use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\FileBundle\Service\ImageManager;
use OHMedia\WysiwygBundle\Twig\AbstractWysiwygExtension;
use Twig\TwigFunction;

class WysiwygExtension extends AbstractWysiwygExtension
{
    public function __construct(
        private FileManager $fileManager,
        private FileRepository $fileRepository,
        private ImageManager $imageManager,
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

        list(
            $width,
            $height,
        ) = $this->imageManager->constrainWidthAndHeight(
            $image,
            $width,
            $height,
        );

        return $this->imageManager->render($image, [
            'width' => $width,
            'height' => $height,
        ]);
    }
}

<?php

namespace OHMedia\FileBundle\Twig;

use Doctrine\ORM\EntityManager;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Entity\ImageResize;
use OHMedia\FileBundle\Service\FileManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
    private $em;
    private $manager;

    public function __construct(EntityManager $em, FileManager $manager)
    {
        $this->em = $em;
        $this->manager = $manager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('oh_media_file', [$this, 'getFile']),
            new TwigFunction('oh_media_image', [$this, 'getImage']),
            new TwigFunction('oh_media_image_tag', [$this, 'getImageTag'], [
                'is_safe' => ['html']
            ])
        ];
    }

    public function getFile(File $file)
    {
        return $this->manager->getWebPath($file);
    }

    public function getImage(Image $image, int $width = null, int $height = null)
    {
        $resize = $this->getImageResize($image, $width, $height);

        $file = $resize
            ? $resize->getFile()
            : $image->getFile();

        return $this->getFile($file);
    }

    public function getImageTag(Image $image, array $attributes = [])
    {
        $width = !empty($attributes['width']) ? $attributes['width'] : null;
        $height = !empty($attributes['height']) ? $attributes['height'] : null;

        $resize = $this->getImageResize($image, $width, $height);

        if ($resize) {
            $file = $resize->getFile();

            $attributes['width'] = $resize->getWidth();
            $attributes['height'] = $resize->getHeight();
        }
        else {
            $file = $image->getFile();

            $attributes['width'] = $file->getWidth();
            $attributes['height'] = $file->getHeight();
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

    private function getImageResize(
        Image $image,
        int $width = null,
        int $height = null
    ): ?ImageResize
    {
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
            // figure out the width
            $width = $height * ($origWidth / $origHeight);
        }
        else if (null === $height) {
            // figure out the height
            $height = $width * ($origHeight / $origWidth);
        }

        $name = sprintf('%sx%s', $width, $height);

        $resize = $image->getResize($name);

        if (!$resize) {
            $copy = $this->manager->copy($image->getFile());

            $resize = new ImageResize();
            $resize
                ->setFile($copy)
                ->setName($name)
                ->setWidth($width)
                ->setHeight($height)
                ->setImage($image);

            $this->em->persist($resize);
            $this->em->flush();
        }

        return $resize;
    }
}

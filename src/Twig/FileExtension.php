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
    
    public function getFunctions()
    {
        return [
            new TwigFunction('ohmedia_file', [$this, 'getFile']),
            new TwigFunction('ohmedia_image', [$this, 'getImage'])
        ];
    }
    
    public function getFile(File $file)
    {
        return $this->manager->getWebPath($file);
    }
    
    public function getImage(Image $image, int $width = null, int $height = null)
    {
        $file = $image->getFile();
        
        if (null === $width && null === $height) {
            return $this->getFile($file);
        }
        
        $path = $this->manager->getAbsolutePath($file);
        
        $imageSize = @getimagesize($path);
        
        if (!$imageSize) {
            return $this->getFile($file);
        }
        
        $origWidth = $imageSize[0];
        $origHeight = $imageSize[1];
        
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
            $copy = $this->manager->copy($file);
            
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
        
        return $this->getFile($resize->getFile());
    }
}

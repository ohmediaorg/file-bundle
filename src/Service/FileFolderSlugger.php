<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Repository\FileFolderRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;

class FileFolderSlugger
{
    private $fileFolderRepository;
    private $slugger;

    public function __construct(FileFolderRepository $fileFolderRepository)
    {
        $this->fileFolderRepository = $fileFolderRepository;
        $this->slugger = new AsciiSlugger();
    }

    public function slug(FileFolder $folder)
    {
        $name = strtolower($folder->getName());

        $slug = $this->slugger->slug($name);

        $i = 1;
        while ($this->fileFolderRepository->countByName($slug, $folder)) {
            $slug = $this->slugger->slug($name.'-'.$i);

            ++$i;
        }

        $folder->setName($slug);
    }
}

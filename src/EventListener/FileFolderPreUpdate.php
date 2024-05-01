<?php

namespace OHMedia\FileBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Service\FileFolderSlugger;

class FileFolderPreUpdate
{
    public function __construct(private FileFolderSlugger $fileFolderSlugger)
    {
    }

    public function preUpdate(FileFolder $folder, PreUpdateEventArgs $args)
    {
        $this->fileFolderSlugger->slug($folder);
    }
}

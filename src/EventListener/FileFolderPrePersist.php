<?php

namespace OHMedia\FileBundle\EventListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Service\FileFolderSlugger;

class FileFolderPrePersist
{
    public function __construct(private FileFolderSlugger $fileFolderSlugger)
    {
    }

    public function prePersist(FileFolder $folder, PrePersistEventArgs $args)
    {
        $this->fileFolderSlugger->slug($folder);
    }
}

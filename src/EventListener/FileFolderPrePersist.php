<?php

namespace OHMedia\FileBundle\EventListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Service\FileFolderSlugger;

class FileFolderPrePersist
{
    private $fileFolderSlugger;

    public function __construct(FileFolderSlugger $fileFolderSlugger)
    {
        $this->fileFolderSlugger = $fileFolderSlugger;
    }

    public function prePersist(FileFolder $folder, PrePersistEventArgs $args)
    {
        $this->fileFolderSlugger->slug($folder);
    }
}

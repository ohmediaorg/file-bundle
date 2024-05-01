<?php

namespace OHMedia\FileBundle\EventListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use OHMedia\FileBundle\Entity\File as FileEntity;
use OHMedia\FileBundle\Service\FileLifecycle;

class FilePrePersist
{
    public function __construct(private FileLifecycle $fileLifecycle)
    {
    }

    public function prePersist(FileEntity $file, PrePersistEventArgs $args)
    {
        $this->fileLifecycle->prePersist($file);
    }
}

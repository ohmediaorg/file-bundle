<?php

namespace OHMedia\FileBundle\EventListener;

use Doctrine\ORM\Event\PostPersistEventArgs;
use OHMedia\FileBundle\Entity\File as FileEntity;
use OHMedia\FileBundle\Service\FileLifecycle;

class FilePostPersist
{
    public function __construct(private FileLifecycle $fileLifecycle)
    {
    }

    public function postPersist(FileEntity $file, PostPersistEventArgs $args)
    {
        $this->fileLifecycle->postPersist($file);
    }
}

<?php

namespace OHMedia\FileBundle\EventListener;

use Doctrine\ORM\Event\PostPersistEventArgs;
use OHMedia\FileBundle\Entity\File as FileEntity;
use OHMedia\FileBundle\Service\FileLifecycle;

class FilePostPersist
{
    private $fileLifecycle;

    public function __construct(FileLifecycle $fileLifecycle)
    {
        $this->fileLifecycle = $fileLifecycle;
    }

    public function postPersist(FileEntity $file, PostPersistEventArgs $args)
    {
        $this->fileLifecycle->postPersist($file);
    }
}

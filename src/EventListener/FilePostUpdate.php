<?php

namespace OHMedia\FileBundle\EventListener;

use Doctrine\ORM\Event\PostUpdateEventArgs;
use OHMedia\FileBundle\Entity\File as FileEntity;
use OHMedia\FileBundle\Service\FileLifecycle;

class FilePostUpdate
{
    private $fileLifecycle;

    public function __construct(FileLifecycle $fileLifecycle)
    {
        $this->fileLifecycle = $fileLifecycle;
    }

    public function postUpdate(FileEntity $file, PostUpdateEventArgs $args)
    {
        $this->fileLifecycle->postUpdate($file);
    }
}

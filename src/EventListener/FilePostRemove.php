<?php

namespace OHMedia\FileBundle\EventListener;

use Doctrine\ORM\Event\PostRemoveEventArgs;
use OHMedia\FileBundle\Entity\File as FileEntity;
use OHMedia\FileBundle\Service\FileLifecycle;

class FilePostRemove
{
    public function __construct(private FileLifecycle $fileLifecycle)
    {
    }

    public function postRemove(FileEntity $file, PostRemoveEventArgs $args)
    {
        $this->fileLifecycle->postRemove($file);
    }
}

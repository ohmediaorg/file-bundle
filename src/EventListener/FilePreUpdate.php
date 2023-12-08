<?php

namespace OHMedia\FileBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use OHMedia\FileBundle\Entity\File as FileEntity;
use OHMedia\FileBundle\Service\FileLifecycle;

class FilePreUpdate
{
    private $fileLifecycle;

    public function __construct(FileLifecycle $fileLifecycle)
    {
        $this->fileLifecycle = $fileLifecycle;
    }

    public function preUpdate(FileEntity $file, PreUpdateEventArgs $args)
    {
        $this->fileLifecycle->preUpdate($file);
    }
}

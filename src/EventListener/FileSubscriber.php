<?php

namespace OHMedia\FileBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\ImageResize;
use OHMedia\FileBundle\Service\FileManager;

class FileSubscriber implements EventSubscriber
{
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
            Events::postUpdate,
            Events::preRemove,
            Events::postRemove,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof File) {
            if ($object->isCloned()) {
                $copy = $this->fileManager->copy($object);

                if (!$copy) {
                    // EDGE CASE: setFile(null) was called then the object was cloned
                    // everything will be null except for $oldPath
                    // we want to set $oldPath to null so the file this was cloned
                    // from is not deleted
                    $object->setOldPath(null);

                    return;
                }

                $this->preSaveFile($copy);

                // transfer important values back to $object
                $object
                    // IMPORTANT: resetting path prior to calling setFile()
                    ->setPath(null)
                    ->setFile($copy->getFile())
                    ->setPath($copy->getPath())
                    ->setToken($copy->getToken())
                ;
            } else {
                if ($object->isBrowser()) {
                    // TODO: determine if Image object should be created
                }

                $this->preSaveFile($object);
            }
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof File) {
            $this->postSaveFile($object);
        } elseif ($object instanceof ImageResize) {
            $this->postSaveImageResize($object);
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof File) {
            $this->preSaveFile($object);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof File) {
            $this->postSaveFile($object);
        } elseif ($object instanceof ImageResize) {
            $this->postSaveImageResize($object);
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof File) {
            // force load the proxy
            $object->__load();
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof File) {
            $this->fileManager->postRemoveFile($object);
        }
    }

    private function preSaveFile(File $file)
    {
        $this->fileManager->preSaveFile($file);
    }

    private function postSaveFile(File $file)
    {
        $this->fileManager->postSaveFile($file);
    }

    private function postSaveImageResize(ImageResize $resize)
    {
        $this->fileManager->postSaveImageResize($resize);
    }
}

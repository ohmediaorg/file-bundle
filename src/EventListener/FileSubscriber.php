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
    private $manager;

    public function __construct(FileManager $manager)
    {
        $this->manager = $manager;
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
                $copy = $this->manager->copy($object);

                $this->preSaveFile($copy);

                // transfer important values back to $object
                $object
                    // IMPORTANT: resetting path prior to calling setFile()
                    ->setPath(File::PATH_INITIAL)
                    ->setFile($copy->getFile())
                    ->setPath($copy->getPath())
                    ->setToken($copy->getToken())
                ;
            } else {
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
            $this->manager->postRemoveFile($object);
        }
    }

    private function preSaveFile(File $file)
    {
        $this->manager->preSaveFile($file);
    }

    private function postSaveFile(File $file)
    {
        $this->manager->postSaveFile($file);
    }

    private function postSaveImageResize(ImageResize $resize)
    {
        $this->manager->postSaveImageResize($resize);
    }
}

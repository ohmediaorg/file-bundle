<?php

namespace JstnThms\FileBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use JstnThms\FileBundle\Entity\File;
use JstnThms\FileBundle\Entity\ImageResize;
use JstnThms\FileBundle\Service\FileManager;

class FileSubscriber implements EventSubscriber
{
    private $manager;
    
    public function __construct(FileManager $manager)
    {
        $this->manager = $manager;
    }
    
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }
    
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        
        if ($object instanceof File) {
            $this->preSaveFile($object);
        }
    }
    
    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        
        if ($object instanceof File) {
            $this->preSaveFile($object);
        }
    }
    
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        
        if ($object instanceof File) {
            $this->postSaveFile($object);
        }
        else if ($object instanceof ImageResize) {
            $this->postSaveImageResize($object);
        }
    }
    
    public function postUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        
        if ($object instanceof File) {
            $this->postSaveFile($object);
        }
        else if ($object instanceof ImageResize) {
            $this->postSaveImageResize($object);
        }
    }
    
    public function postRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        
        if ($object instanceof File) {
            $this->postRemoveFile($object);
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

    private function postRemoveFile(File $file)
    {
        $this->manager->postRemoveFile($file);
    }
}

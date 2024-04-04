<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\SecurityBundle\Service\EntityChoiceInterface;

class FileEntityChoice implements EntityChoiceInterface
{
    public function getLabel(): string
    {
        return 'Files';
    }

    public function getEntities(): array
    {
        return [
            File::class,
            FileFolder::class,
        ];
    }
}

<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;

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

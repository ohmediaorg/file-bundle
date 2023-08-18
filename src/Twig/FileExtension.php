<?php

namespace OHMedia\FileBundle\Twig;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\FileBundle\Util\FileUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_file_entity', [$this, 'isFileEntity']),
            new TwigFunction('is_file_folder_entity', [$this, 'isFileFolderEntity']),
            new TwigFunction('file_size', [$this, 'getFileSize']),
            new TwigFunction('file_size_binary', [$this, 'getFileSizeBinary']),
            new TwigFunction('file_path', [$this, 'getFilePath']),
        ];
    }

    public function isFileEntity(mixed $value): bool
    {
        return $value instanceof File;
    }

    public function isFileFolderEntity(mixed $value): bool
    {
        return $value instanceof FileFolder;
    }

    public function getFileSize(File $file, int $precision = 1): string
    {
        return FileUtil::formatBytesDecimal($file->getSize(), $precision);
    }

    public function getFileSizeBinary(File $file, int $precision = 1): string
    {
        return FileUtil::formatBytesBinary($file->getSize(), $precision);
    }

    public function getFilePath(File $file): ?string
    {
        return $this->fileManager->getWebPath($file);
    }
}

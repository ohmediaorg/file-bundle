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
            new TwigFunction('file_size', [$this, 'fileSize']),
            new TwigFunction('file_size_binary', [$this, 'fileSizeBinary']),
            new TwigFunction('format_bytes', [$this, 'formatBytes']),
            new TwigFunction('format_bytes_binary', [$this, 'formatBytesBinary']),
            new TwigFunction('file_path', [$this, 'filePath']),
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

    public function fileSize(File $file, int $precision = 1): string
    {
        return FileUtil::formatBytesDecimal($file->getSize(), $precision);
    }

    public function fileSizeBinary(File $file, int $precision = 1): string
    {
        return FileUtil::formatBytesBinary($file->getSize(), $precision);
    }

    public function formatBytes(int $bytes, int $precision = 1): string
    {
        return FileUtil::formatBytesDecimal($bytes, $precision);
    }

    public function formatBytesBinary(int $bytes, int $precision = 1): string
    {
        return FileUtil::formatBytesBinary($bytes, $precision);
    }

    public function filePath(File $file): ?string
    {
        return $this->fileManager->getWebPath($file);
    }
}

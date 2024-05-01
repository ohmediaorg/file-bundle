<?php

namespace OHMedia\FileBundle\Twig;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Service\FileBrowser;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\FileBundle\Util\FileUtil;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
    public function __construct(
        private FileBrowser $fileBrowser,
        private FileManager $fileManager
    ) {
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
            new TwigFunction('file_limit', [$this, 'fileLimit'], [
                'is_safe' => ['html'],
                'needs_environment' => 'true',
            ]),
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

    public function fileLimit(Environment $twig)
    {
        $limit = $this->fileBrowser->getLimitBytes();

        $usage = $this->fileBrowser->getUsageBytes();

        $percent = round(($usage / $limit) * 100);

        if ($percent > 100) {
            $percent = 100;
        }

        if ($percent > 90) {
            $className = 'danger';
        } elseif ($percent > 60) {
            $className = 'warning';
        } else {
            $className = 'success';
        }

        return $twig->render('@OHMediaFile/file_limit.html.twig', [
            'usage' => $usage,
            'limit' => $limit,
            'percent' => $percent,
            'class_name' => $className,
        ]);
    }
}

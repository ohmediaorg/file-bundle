<?php

namespace OHMedia\FileBundle\Util;

class FileUtil
{
    public static function getExtension($filepath): string
    {
        return strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    }

    public static function getTargetWidth(int $origW, int $origH, int $targetH): int
    {
        return 0 !== $origH
            ? ($origW / $origH) * $targetH
            : 0;
    }

    public static function getTargetHeight(int $origW, int $origH, int $targetW): int
    {
        return 0 !== $origW
            ? ($origH / $origW) * $targetW
            : 0;
    }

    public static function formatBytesBinary(int $bytes, int $precision)
    {
        return self::formatBytes($bytes, $precision, true);
    }

    public static function formatBytesDecimal(int $bytes, int $precision)
    {
        return self::formatBytes($bytes, $precision, false);
    }

    private static function formatBytes(int $bytes, int $precision, bool $binary): string
    {
        if ($binary) {
            $units = array('B', 'KiB', 'MiB', 'GiB');
            $base = 1024;
        }
        else {
            $units = array('B', 'kB', 'MB', 'GB');
            $base = 1000;
        }

        $unit = 0;
        $maxUnit = count($units);

        while ($bytes > $base && $unit < $maxUnit) {
            $bytes /= $base;
            $unit++;
        }

        return round($bytes, $precision) . ' ' . $units[$unit];
    }
}

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

    public static function formatBytes(?int $bytes, $precision): string
    {
        if (!$bytes) {
            return '0 B';
        }

        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $mult = 1024;
        $unit = 0;
        $maxUnit = count($units);

        while ($bytes > $mult && $unit < $maxUnit) {
            $bytes /= $mult;
            $unit++;
        }

        return round($bytes, $precision) . ' ' . $units[$unit];
    }
}

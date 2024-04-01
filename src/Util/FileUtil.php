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
            ? intval(($origW / $origH) * $targetH)
            : 0;
    }

    public static function getTargetHeight(int $origW, int $origH, int $targetW): int
    {
        return 0 !== $origW
            ? intval(($origH / $origW) * $targetW)
            : 0;
    }

    public static function getBytes(string $byteString)
    {
        $byteString = trim($byteString);

        if (is_numeric($byteString)) {
            return $byteString;
        }

        $last = strtolower($byteString[strlen($byteString) - 1]);
        $byteString = rtrim($byteString, 'b');
        $bytes = substr($byteString, 0, -1);

        switch ($last) {
            case 'g':
                $bytes *= 1024;
                // no break
            case 'm':
                $bytes *= 1024;
                // no break
            case 'k':
                $bytes *= 1024;
        }

        return $bytes;
    }

    public static function formatBytesBinary(int $bytes, int $precision): string
    {
        return self::formatBytes($bytes, $precision, true);
    }

    public static function formatBytesDecimal(int $bytes, int $precision): string
    {
        return self::formatBytes($bytes, $precision, false);
    }

    private static function formatBytes(int $bytes, int $precision, bool $binary): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        if ($binary) {
            $base = 1024;
        } else {
            $base = 1000;
        }

        $unit = 0;
        $maxUnit = count($units);

        $bytes = abs($bytes);

        while ($bytes >= $base && $unit < $maxUnit) {
            $bytes /= $base;
            ++$unit;
        }

        return round($bytes, $precision).' '.$units[$unit];
    }
}

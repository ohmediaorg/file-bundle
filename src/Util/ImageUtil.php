<?php

namespace OHMedia\FileBundle\Util;

class ImageUtil
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
}

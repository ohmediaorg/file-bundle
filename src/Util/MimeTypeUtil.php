<?php

namespace OHMedia\FileBundle\Util;

use Symfony\Component\Validator\Constraints\File as FileConstraint;

class MimeTypeUtil
{
    // supported by <audio> element
    public const AUDIO = [
        'audio/mpeg' => 'mp3',
        'audio/ogg' => 'oga',
        'audio/wav' => 'wav',
    ];

    public const PDF = 'application/pdf';

    public const DOCUMENT = [
        'application/x-abiword' => 'abw',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-fontobject' => 'eot',
        'application/vnd.oasis.opendocument.presentation' => 'odp',
        'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
        'application/vnd.oasis.opendocument.text' => 'odt',
        self::PDF => 'pdf',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/rtf' => 'rtf',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    ];

    public const SVG = 'image/svg+xml';

    public const IMAGE = [
        'image/gif' => 'gif',
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        self::SVG => 'svg',
    ];

    public const TEXT = [
        'application/csv' => 'csv',
        'text/csv' => 'csv',
        'text/calendar' => 'ics',
        'text/plain' => 'txt',
    ];

    // supported by <video> element
    public const VIDEO = [
        'video/mp4' => 'mp4',
        'video/ogg' => 'ogv',
        'video/webm' => 'webm',
    ];

    public static function getMimeTypes(array ...$consts): array
    {
        $mimeTypes = [];

        foreach ($consts as $const) {
            $mimeTypes = array_merge($mimeTypes, array_keys($const));
        }

        sort($mimeTypes);

        return array_unique($mimeTypes);
    }

    public static function getExtensions(array ...$consts): array
    {
        $exts = [];

        foreach ($consts as $const) {
            $exts = array_merge($exts, array_values($const));
        }

        sort($exts);

        return array_unique($exts);
    }

    public static function getFileConstraint(array ...$consts)
    {
        $mimeTypes = self::getMimeTypes(...$consts);
        $exts = self::getExtensions(...$consts);

        $lastExt = array_pop($exts);

        if ($exts) {
            $exts = implode(', ', $exts);

            $mimeTypesMessage = sprintf(
                'Only %s, and %s are accepted for upload.',
                strtoupper($exts),
                strtoupper($lastExt)
            );
        } else {
            $mimeTypesMessage = sprintf(
                'Only %s is accepted for upload.',
                strtoupper($lastExt)
            );
        }

        return new FileConstraint([
            'mimeTypes' => $mimeTypes,
            'mimeTypesMessage' => $mimeTypesMessage,
        ]);
    }

    public static function getAllFileConstraint(): FileConstraint
    {
        return self::getFileConstraint(
            self::AUDIO,
            self::DOCUMENT,
            self::IMAGE,
            self::TEXT,
            self::VIDEO
        );
    }

    public static function getImageFileConstraint(): FileConstraint
    {
        return self::getFileConstraint(self::IMAGE);
    }
}

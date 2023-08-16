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

    public const DOCUMENT = [
        'application/x-abiword' => 'abw',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-fontobject' => 'eot',
        'application/vnd.oasis.opendocument.presentation' => 'odp',
        'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
        'application/vnd.oasis.opendocument.text' => 'odt',
        'application/pdf' => 'pdf',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/rtf' => 'rtf',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    ];

    // supported by Image entity
    public const IMAGE = [
        'image/gif' => 'gif',
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/svg+xml' => 'svg',
    ];

    public const TEXT = [
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

        return $mimeTypes;
    }

    public static function getExtensions(array ...$consts): array
    {
        $exts = [];

        foreach ($consts as $const) {
            $exts = array_merge($exts, array_values($const));
        }

        return $exts;
    }

    public static function getFileConstraint(array ...$consts)
    {
        $mimeTypes = self::getMimeTypes(...$consts);
        $exts = self::getExtensions(...$consts);;

        return new FileConstraint([
            'mimeTypes' => $mimeTypes,
            'mimeTypesMessage' => sprintf(
                'Only %s are accepted for upload.',
                strtoupper(implode('/', $exts)),
            ),
        ]),
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

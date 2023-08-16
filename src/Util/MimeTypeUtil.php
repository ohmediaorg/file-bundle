<?php

namespace OHMedia\FileBundle\Util;

use Symfony\Component\Validator\Constraints\File as FileConstraint;

class MimeTypeUtil
{
    // supported by <audio> element
    private const AUDIO = [
        'audio/mpeg' => 'mp3',
        'audio/ogg' => 'oga',
        'audio/wav' => 'wav',
    ];

    private const DOCUMENT = [
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
    private const IMAGE = [
        'image/gif' => 'gif',
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/svg+xml' => 'svg',
    ];

    private const TEXT = [
        'text/csv' => 'csv',
        'text/calendar' => 'ics',
        'text/plain' => 'txt',
    ];

    // supported by <video> element
    private const VIDEO = [
        'video/mp4' => 'mp4',
        'video/ogg' => 'ogv',
        'video/webm' => 'webm',
    ];

    public static function getAudioMimeTypes(): array
    {
        return array_keys(self::AUDIO);
    }

    public static function getAudioExtensions(): array
    {
        return array_values(self::AUDIO);
    }

    public static function getDocumentMimeTypes(): array
    {
        return array_keys(self::DOCUMENT);
    }

    public static function getDocumentExtensions(): array
    {
        return array_values(self::DOCUMENT);
    }

    public static function getImageMimeTypes(): array
    {
        return array_keys(self::IMAGE);
    }

    public static function getImageExtensions(): array
    {
        return array_values(self::IMAGE);
    }

    public static function getTextMimeTypes(): array
    {
        return array_keys(self::TEXT);
    }

    public static function getTextExtensions(): array
    {
        return array_values(self::TEXT);
    }

    public static function getVideoMimeTypes(): array
    {
        return array_keys(self::VIDEO);
    }

    public static function getVideoExtensions(): array
    {
        return array_values(self::VIDEO);
    }

    public static function getImageFileConstraint(): FileConstraint
    {
        $imageMimeTypes = self::getImageMimeTypes();
        $imageExtensions = self::getImageExtensions();

        return new FileConstraint([
            'mimeTypes' => $imageMimeTypes,
            'mimeTypesMessage' => sprintf(
                'Only %s is accepted for upload.',
                strtoupper(implode('/', $imageExtensions)),
            ),
        ]),
    }
}

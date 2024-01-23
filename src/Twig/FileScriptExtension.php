<?php

namespace OHMedia\FileBundle\Twig;

use OHMedia\FileBundle\Form\Type\FileEntityType;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileScriptExtension extends AbstractExtension
{
    private $rendered = false;

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_script', [$this, 'script'], [
                'is_safe' => ['html'],
                'needs_environment' => 'true',
            ]),
        ];
    }

    public function script(Environment $twig)
    {
        if ($this->rendered) {
            return '';
        }

        $this->rendered = true;

        return $twig->render('@OHMediaFile/file_script.html.twig', [
            'ACTION_REPLACE' => FileEntityType::ACTION_REPLACE,
            'DATA_ATTRIBUTE' => FileEntityType::DATA_ATTRIBUTE,
        ]);
    }
}

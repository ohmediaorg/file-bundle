<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\BackendBundle\ContentLinks\AbstractContentLinkProvider;
use OHMedia\BackendBundle\ContentLinks\ContentLink;
use OHMedia\FileBundle\Entity\FileFolder;

class FileContentLinkProvider extends AbstractContentLinkProvider
{
    public function __construct(private FileBrowser $fileBrowser)
    {
    }

    public function getTitle(): string
    {
        return 'Files';
    }

    public function buildContentLinks(): void
    {
        if (!$this->fileBrowser->isEnabled()) {
            return;
        }

        $contentLinks = $this->createContentLinks();

        foreach ($contentLinks as $contentLink) {
            $this->addContentLink($contentLink);
        }
    }

    private function createContentLinks(FileFolder $fileFolder = null): array
    {
        $items = $this->fileBrowser->getListing($fileFolder);

        $contentLinks = [];

        foreach ($items as $item) {
            $id = $item->getId();

            $title = (string) $item;

            $contentLink = new ContentLink($title);

            if ($item instanceof FileFolder) {
                $children = $this->createContentLinks($item);

                $contentLink->setChildren(...$children);

                if ($children) {
                    $contentLinks[] = $contentLink;
                }
            } else {
                $contentLink->setShortcode('file_href('.$id.')');

                $contentLinks[] = $contentLink;
            }
        }

        return $contentLinks;
    }
}

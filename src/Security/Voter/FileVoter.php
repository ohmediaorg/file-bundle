<?php

namespace OHMedia\FileBundle\Security\Voter;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Service\FileBrowser;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;
use OHMedia\WysiwygBundle\Service\Wysiwyg;

class FileVoter extends AbstractEntityVoter
{
    public const INDEX = 'index';
    public const CREATE = 'create';
    public const EDIT = 'edit';
    public const LOCK = 'lock';
    public const UNLOCK = 'unlock';
    public const MOVE = 'move';
    public const DELETE = 'delete';

    public function __construct(
        private FileBrowser $fileBrowser,
        private Wysiwyg $wysiwyg
    ) {
    }

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::EDIT,
            self::LOCK,
            self::UNLOCK,
            self::MOVE,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return File::class;
    }

    protected function canIndex(File $file, User $loggedIn): bool
    {
        return $file->isBrowser() && $this->fileBrowser->isEnabled();
    }

    protected function canCreate(File $file, User $loggedIn): bool
    {
        if (!$file->isBrowser()) {
            return false;
        }

        if (!$this->fileBrowser->isEnabled()) {
            return false;
        }

        return $this->fileBrowser->getLimitBytes() > $this->fileBrowser->getUsageBytes();
    }

    protected function canEdit(File $file, User $loggedIn): bool
    {
        // only for editing image alt text
        return $file->isBrowser() && $file->isImage() && $this->fileBrowser->isEnabled();
    }

    protected function canLock(File $file, User $loggedIn): bool
    {
        return $file->isBrowser() && !$file->isLocked() && $this->fileBrowser->isEnabled();
    }

    protected function canUnlock(File $file, User $loggedIn): bool
    {
        if (!$file->isBrowser()) {
            return false;
        }

        if (!$this->fileBrowser->isEnabled()) {
            return false;
        }

        $parent = $file->getFolder();

        if ($parent && $parent->isLocked()) {
            return false;
        }

        return $file->isLocked();
    }

    protected function canMove(File $file, User $loggedIn): bool
    {
        return $file->isBrowser() && $this->fileBrowser->isEnabled();
    }

    protected function canDelete(File $file, User $loggedIn): bool
    {
        if (!$file->isBrowser()) {
            return false;
        }

        if (!$this->fileBrowser->isEnabled()) {
            return false;
        }

        $shortcodes = [
            sprintf('file_href(%d)', $file->getId()),
            sprintf('image(%d)', $file->getId()),
        ];

        return !$this->wysiwyg->shortcodesInUse(...$shortcodes);
    }
}

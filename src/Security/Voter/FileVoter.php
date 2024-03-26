<?php

namespace OHMedia\FileBundle\Security\Voter;

use OHMedia\FileBundle\Entity\File;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;

class FileVoter extends AbstractEntityVoter
{
    public const INDEX = 'index';
    public const CREATE = 'create';
    // NOTE: this is only for frontend; there is no backend file view
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const LOCK = 'lock';
    public const UNLOCK = 'unlock';
    public const MOVE = 'move';
    public const DELETE = 'delete';

    private bool $fileBrowserEnabled;

    public function __construct(bool $fileBrowserEnabled)
    {
        $this->fileBrowserEnabled = $fileBrowserEnabled;
    }

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::VIEW,
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
        return $file->isBrowser() && $this->fileBrowserEnabled;
    }

    protected function canCreate(File $file, User $loggedIn): bool
    {
        return $file->isBrowser() && $this->fileBrowserEnabled;
    }

    protected function canView(File $file, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(File $file, User $loggedIn): bool
    {
        // only for editing image alt text
        return $file->isBrowser() && $file->isImage() && $this->fileBrowserEnabled;
    }

    protected function canLock(File $file, User $loggedIn): bool
    {
        return $file->isBrowser() && !$file->isLocked() && $this->fileBrowserEnabled;
    }

    protected function canUnlock(File $file, User $loggedIn): bool
    {
        if (!$file->isBrowser()) {
            return false;
        }

        if (!$this->fileBrowserEnabled) {
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
        return $file->isBrowser() && $this->fileBrowserEnabled;
    }

    protected function canDelete(File $file, User $loggedIn): bool
    {
        return $file->isBrowser() && $this->fileBrowserEnabled;
    }
}

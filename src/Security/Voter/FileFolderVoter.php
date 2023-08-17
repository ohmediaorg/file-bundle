<?php

namespace OHMedia\FileBundle\Security\Voter;

use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;

class FileFolderVoter extends AbstractEntityVoter
{
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const LOCK = 'lock';
    public const UNLOCK = 'unlock';
    public const MOVE = 'move';
    public const DELETE = 'delete';

    protected function getAttributes(): array
    {
        return [
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
        return FileFolder::class;
    }

    protected function canCreate(FileFolder $folder, User $loggedIn): bool
    {
        return $folder->isBrowser();
    }

    protected function canView(FileFolder $folder, User $loggedIn): bool
    {
        return $folder->isBrowser();
    }

    protected function canEdit(FileFolder $folder, User $loggedIn): bool
    {
        return $folder->isBrowser();
    }

    protected function canLock(FileFolder $folder, User $loggedIn): bool
    {
        return $folder->isBrowser() && !$folder->isLocked();
    }

    protected function canUnlock(FileFolder $folder, User $loggedIn): bool
    {
        if (!$folder->isBrowser()) {
            return false;
        }

        $parent = $folder->getFolder();

        if ($parent && $parent->isLocked()) {
            return false;
        }

        return $folder->isLocked();
    }

    protected function canMove(FileFolder $folder, User $loggedIn): bool
    {
        return $folder->isBrowser();
    }

    protected function canDelete(FileFolder $folder, User $loggedIn): bool
    {
        return $folder->isBrowser();
    }
}

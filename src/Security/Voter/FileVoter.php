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
    public const LOCK = 'lock';
    public const UNLOCK = 'unlock';
    public const MOVE = 'move';
    public const DELETE = 'delete';

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::VIEW,
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
        return $file->isBrowser();
    }

    protected function canCreate(File $file, User $loggedIn): bool
    {
        return $file->isBrowser();
    }

    protected function canView(File $file, User $loggedIn): bool
    {
        return true;
    }

    protected function canLock(File $file, User $loggedIn): bool
    {
        return $file->isBrowser() && !$file->isLocked();
    }

    protected function canUnlock(File $file, User $loggedIn): bool
    {
        // TODO: if the parent is locked, you can't unlock
        return $file->isBrowser() && $file->isLocked();
    }

    protected function canMove(File $file, User $loggedIn): bool
    {
        return $file->isBrowser();
    }

    protected function canDelete(File $file, User $loggedIn): bool
    {
        return $file->isBrowser();
    }
}

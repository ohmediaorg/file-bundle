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
    public const MOVE = 'move';
    public const DELETE = 'delete';

    protected function getAttributes(): array
    {
        return [
            self::CREATE,
            self::VIEW,
            self::EDIT,
            self::MOVE,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return FileFolder::class;
    }

    protected function canCreate(FileFolder $file, User $loggedIn): bool
    {
        return $file->isBrowser();
    }

    protected function canView(FileFolder $file, User $loggedIn): bool
    {
        return $file->isBrowser();
    }

    protected function canEdit(FileFolder $file, User $loggedIn): bool
    {
        return $file->isBrowser();
    }

    protected function canMove(FileFolder $file, User $loggedIn): bool
    {
        return $file->isBrowser();
    }

    protected function canDelete(FileFolder $file, User $loggedIn): bool
    {
        return $file->isBrowser();
    }
}

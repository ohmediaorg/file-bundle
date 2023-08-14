<?php

namespace OHMedia\FileBundle\Security\Voter;

use OHMedia\FileBundle\Entity\File;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;

class FileVoter extends AbstractEntityVoter
{
    public const INDEX = 'index';
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const COPY = 'copy';
    public const DELETE = 'delete';

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::VIEW,
            self::EDIT,
            self::COPY,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return File::class;
    }

    protected function canIndex(File $file, User $loggedIn): bool
    {
        return true;
    }

    protected function canCreate(File $file, User $loggedIn): bool
    {
        return true;
    }

    protected function canView(File $file, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(File $file, User $loggedIn): bool
    {
        // TODO: prevent uploaded file from being changed?
        return true;
    }

    protected function canCopy(File $file, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(File $file, User $loggedIn): bool
    {
        return true;
    }
}

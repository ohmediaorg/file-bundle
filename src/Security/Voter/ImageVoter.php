<?php

namespace OHMedia\FileBundle\Security\Voter;

use OHMedia\FileBundle\Entity\Image;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;

class ImageVoter extends AbstractEntityVoter
{
    public const CREATE = 'create';
    public const EDIT = 'edit';

    protected function getAttributes(): array
    {
        return [
            self::CREATE,
            self::EDIT,
        ];
    }

    protected function getEntityClass(): string
    {
        return Image::class;
    }

    protected function canCreate(Image $image, User $loggedIn): bool
    {
        return $image->isBrowser();
    }

    protected function canEdit(Image $image, User $loggedIn): bool
    {
        return $image->isBrowser();
    }
}

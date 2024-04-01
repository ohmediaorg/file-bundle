<?php

namespace OHMedia\FileBundle\Security\Voter;

use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Service\FileBrowser;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FileFolderVoter extends AbstractEntityVoter
{
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const LOCK = 'lock';
    public const UNLOCK = 'unlock';
    public const MOVE = 'move';
    public const DELETE = 'delete';

    private AuthorizationCheckerInterface $authorizationChecker;
    private FileBrowser $fileBrowser;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, FileBrowser $fileBrowser)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->fileBrowser = $fileBrowser;
    }

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
        return $folder->isBrowser() && $this->fileBrowser->isEnabled();
    }

    protected function canView(FileFolder $folder, User $loggedIn): bool
    {
        return $folder->isBrowser() && $this->fileBrowser->isEnabled();
    }

    protected function canEdit(FileFolder $folder, User $loggedIn): bool
    {
        return $folder->isBrowser() && $this->fileBrowser->isEnabled();
    }

    protected function canLock(FileFolder $folder, User $loggedIn): bool
    {
        return $folder->isBrowser() && !$folder->isLocked() && $this->fileBrowser->isEnabled();
    }

    protected function canUnlock(FileFolder $folder, User $loggedIn): bool
    {
        if (!$folder->isBrowser()) {
            return false;
        }

        if (!$this->fileBrowser->isEnabled()) {
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
        return $folder->isBrowser() && $this->fileBrowser->isEnabled();
    }

    protected function canDelete(FileFolder $folder, User $loggedIn): bool
    {
        if (!$folder->isBrowser()) {
            return false;
        }

        if (!$this->fileBrowser->isEnabled()) {
            return false;
        }

        foreach ($folder->getFiles() as $file) {
            if (!$this->authorizationChecker->isGranted(FileVoter::DELETE, $file)) {
                return false;
            }
        }

        foreach ($folder->getFolders() as $child) {
            if (!$this->canDelete($child, $loggedIn)) {
                return false;
            }
        }

        return true;
    }
}

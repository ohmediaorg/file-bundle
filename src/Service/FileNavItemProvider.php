<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\BackendBundle\Service\AbstractNavItemProvider;
use OHMedia\BootstrapBundle\Component\Nav\NavItemInterface;
use OHMedia\BootstrapBundle\Component\Nav\NavLink;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Security\Voter\FileVoter;

class FileNavItemProvider extends AbstractNavItemProvider
{
    public function getNavItem(): ?NavItemInterface
    {
        $file = (new File())->setBrowser(true);

        if ($this->isGranted(FileVoter::INDEX, $file)) {
            return (new NavLink('Files', 'file_index'))
                ->setIcon('folder-fill');
        }

        return null;
    }
}

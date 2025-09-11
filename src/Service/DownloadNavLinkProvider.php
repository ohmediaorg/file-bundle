<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\BackendBundle\Service\AbstractDeveloperOnlyNavLinkProvider;
use OHMedia\BootstrapBundle\Component\Nav\NavLink;

class DownloadNavLinkProvider extends AbstractDeveloperOnlyNavLinkProvider
{
    public function getNavLink(): NavLink
    {
        return (new NavLink('Download File Listing', 'file_download'))
            ->setIcon('download');
    }

    public function getVoterAttribute(): string
    {
        return 'IS_AUTHENTICATED_FULLY';
    }

    public function getVoterSubject(): mixed
    {
        return null;
    }
}

<?php

namespace OHMedia\FileBundle\Command;

use OHMedia\FileBundle\Entity\File;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ohmedia:file:cleanup')
            ->setDescription('Remove temporary files that are a day old')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        
        $em->getRepository(File::class)->deleteTemporary();
    }
}

<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ThumbnailCommand extends ContainerAwareCommand {
    
    protected function configure() {
        $this
            ->setName('thumbnail:generate')
            ->setDescription('Generates thumbnails for equipments and talents');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getContainer()->get('thumbnail')->run();
    }
}

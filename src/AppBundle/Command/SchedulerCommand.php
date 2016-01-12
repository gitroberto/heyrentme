<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerCommand extends ContainerAwareCommand {
    
    protected function configure() {
        $this
            ->setName('scheduler:run')
            ->setDescription('Run scheduler');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getContainer()->get('scheduler')->run();
    }
}

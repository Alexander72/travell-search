<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateCitiesCommand extends Command
{
    protected static $defaultName = 'updateCities';

    protected $apiToken;

    public function __construct($apiToken = null, $name = null)
    {
        parent::__construct($name);
        $this->apiToken = $apiToken;
    }

    protected function configure()
    {
        $this->setDescription('Loads cities from Aviasales');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);


        $io->success($this->apiToken);
    }
}

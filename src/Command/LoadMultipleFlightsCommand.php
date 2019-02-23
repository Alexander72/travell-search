<?php

namespace App\Command;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadMultipleFlightsCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected static $defaultName = 'LoadMultipleFlights';

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->em = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Loads multiple flights using LoadFlight command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $command = $this->getApplication()->find(LoadFlightsCommand::getDefaultName());

        $europeCities = $this->em->getRepository(City::class)->getEuropeCities();

        $i = 0;
        foreach($europeCities as $origin)
        {
            foreach($europeCities as $destination)
            {
                if($origin->getCode() == $destination->getCode())
                {
                    continue;
                }

                $arguments = [
                    'command' => LoadFlightsCommand::getDefaultName(),
                    '--origin' => $origin->getCode(),
                    '--destination' => $destination->getCode(),
                ];

                $greetInput = new ArrayInput($arguments);

                try
                {
                    $command->run($greetInput, $output);
                } catch(\Exception $e) {
                    continue;
                }

                $i++;
                \usleep(500);

                if($i > 500)
                {
                    break;
                }
            }
        }

        $io->success('Flights loaded successfully.');
    }
}

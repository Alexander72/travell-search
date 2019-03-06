<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\LoadFlightsCommandState;
use App\Generators\CitiesGenerator;
use App\Repository\CityRepository;
use App\Repository\LoadFlightsCommandStateRepository;
use ArrayIterator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\Factory;

class LoadMultipleFlightsCommand extends Command
{
    const LOCK_NAME = 'loadMultipleFlightsLock';

    const WAIT_TIME_BETWEEN_LOAD_FLIGHTS_COMMAND_CALLS_IN_MICROSECONDS = 0;//300*1000;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Factory
     */
    private $lockFactory;

    protected static $defaultName = 'LoadMultipleFlights';

    public function __construct(
        EntityManagerInterface $entityManager,
        Factory $lockFactory
    ) {
        parent::__construct();
        $this->em = $entityManager;
        $this->lockFactory = $lockFactory;
    }

    protected function configure()
    {
        $this->setDescription('Loads multiple flights using LoadFlight command');
        $this->addArgument('depart_month_first_day', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $lock = $this->lockFactory->createLock(self::LOCK_NAME);

        if(!$lock->acquire())
        {
            throw new Exception('Cannot acquire the lock. Probably another instance of command is running.');
        }

        try {
            $command = $this->getLoadFlightsCommand();

            /** @var CityRepository $cityRepository */
            $cityRepository = $this->em->getRepository(City::class);
            $europeCities = $cityRepository->getLargeEuropeCities();
            $state = $this->getState($europeCities, $input->getArgument('depart_month_first_day'));

            $originCities = new CitiesGenerator($europeCities, $state->getOrigin());
            $destinationCities = new CitiesGenerator($europeCities, $state->getDestination());

            foreach($originCities->get() as $origin)
            {
                foreach($destinationCities->get() as $destination)
                {
                    if($origin->getCode() == $destination->getCode())
                    {
                        continue;
                    }

                    $state->update($origin, $destination);

                    try
                    {
                        $loadFlightsCommandArguments = $this->getLoadFlightsCommandArguments($input, $origin, $destination);
                        $command->run($loadFlightsCommandArguments, $output);
                    } catch(\Exception $e) {
                        $io->warning($e->getMessage());
                    }

                    $lock->refresh();

                    \usleep(self::WAIT_TIME_BETWEEN_LOAD_FLIGHTS_COMMAND_CALLS_IN_MICROSECONDS);
                }

                $destinationCities->reset();

                $this->em->flush();
            }

            $state->finish();
            $this->em->flush();

            $io->success('Flights loaded successfully.');
        } finally {
            $lock->release();
        }
    }

    private function getState(array $cities, string $departMonthFirstDay): LoadFlightsCommandState
    {
        $departMonthFirstDay = DateTime::createFromFormat('Y-m-d', $departMonthFirstDay);
        /** @var LoadFlightsCommandStateRepository $loadStateRepository */
        $loadStateRepository = $this->em->getRepository(LoadFlightsCommandState::class);
        $state = $loadStateRepository->getLoadMultipleFlightsCommandState($departMonthFirstDay);
        if(!$state)
        {
            $state = new LoadFlightsCommandState();
            $state->setParams([
                'cities' => array_map(function($city){return $city->getCode();}, $cities),
            ]);
            $state->setDepartMonthFirstDay($departMonthFirstDay);
            $this->em->persist($state);
        }

        return $state;
    }

    /**
     * @return Command
     */
    private function getLoadFlightsCommand(): Command
    {
        return $this->getApplication()->find(LoadFlightsCommand::getDefaultName());
    }

    /**
     * @param InputInterface $input
     * @param City           $origin
     * @param City           $destination
     *
     * @return ArrayInput
     */
    private function getLoadFlightsCommandArguments(InputInterface $input, City $origin, City $destination): ArrayInput
    {
        $arguments = [
            'command'        => LoadFlightsCommand::getDefaultName(),
            '--origin'       => $origin->getCode(),
            '--destination'  => $destination->getCode(),
            '--depart_month' => $input->getArgument('depart_month_first_day'),
        ];

        $greetInput = new ArrayInput($arguments);

        return $greetInput;
    }
}

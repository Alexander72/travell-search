<?php

namespace App\Command;

use App\Builders\CitiesGeneratorBuilder;
use App\Entity\City;
use App\Entity\LoadFlightsCommandState;
use App\Generators\CitiesGenerator;
use App\Repository\CityRepository;
use App\Repository\LoadFlightsCommandStateRepository;
use App\Builders\FlightLoadStateBuilder;
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

    const WAIT_TIME_BETWEEN_LOAD_FLIGHTS_COMMAND_CALLS_IN_MICROSECONDS = 300*1000;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Factory
     */
    private $lockFactory;

    /**
     * @var \App\Builders\FlightLoadStateBuilder
     */
    private $stateBuilder;

    /**
     * @var CitiesGeneratorBuilder
     */
    private $citiesGeneratorBuilder;

    protected static $defaultName = 'LoadMultipleFlights';

    public function __construct(
        EntityManagerInterface $entityManager,
        Factory $lockFactory,
        FlightLoadStateBuilder $stateService,
        CitiesGeneratorBuilder $citiesGeneratorBuilder
    ) {
        parent::__construct();
        $this->em = $entityManager;
        $this->lockFactory = $lockFactory;
        $this->stateBuilder = $stateService;
        $this->citiesGeneratorBuilder = $citiesGeneratorBuilder;
    }

    protected function configure()
    {
        $this->setDescription('Loads multiple flights using LoadFlight command');
        $this->addArgument('depart_month_first_day', InputArgument::OPTIONAL);
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

            $this->stateBuilder->setDefaultOriginCities($this->getOriginCities());
            $this->stateBuilder->setDefaultDestinationCities($this->getDestinationCities());
            $this->stateBuilder->setDepartureMonthFirstDay($this->getDepartureMonthFirstDay($input));
            $state = $this->stateBuilder->build();

            $this->citiesGeneratorBuilder->setState($state);
            $originCities = $this->citiesGeneratorBuilder->buildOriginsGenerator();
            $destinationCities = $this->citiesGeneratorBuilder->buildDestinationsGenerator();

            //$state->setOrigins($originCities);
            //$state->setDestinations($destinationCities);

            //$originCities = new CitiesGenerator($originCities, $state->getOrigin());
            //$destinationCities = new CitiesGenerator($destinationCities, $state->getDestination());

            foreach($originCities->yield() as $origin)
            {
                foreach($destinationCities->yield() as $destination)
                {
                    if($origin->getCode() == $destination->getCode())
                    {
                        continue;
                    }

                    $state->update($origin, $destination);

                    try
                    {
                        $loadFlightsCommandArguments = $this->getLoadFlightsCommandArguments($state);
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

    private function getOriginCities(): array
    {
        /** @var CityRepository $cityRepository */
        $cityRepository = $this->em->getRepository(City::class);
        return $cityRepository->getEuropeCitiesForSearch();
    }

    private function getDestinationCities(): array
    {
        return $this->getOriginCities();
    }

    private function loadState(array $originCities, array $destinationCities, string $departMonthFirstDay): LoadFlightsCommandState
    {
        $departMonthFirstDay = DateTime::createFromFormat('Y-m-d', $departMonthFirstDay);
        /** @var LoadFlightsCommandStateRepository $loadStateRepository */
        $loadStateRepository = $this->em->getRepository(LoadFlightsCommandState::class);
        $state = $loadStateRepository->getLoadMultipleFlightsCommandState($departMonthFirstDay);
        if(!$state)
        {
            $state = new LoadFlightsCommandState();
            $state->setParams([
                'origins' => array_map(function($city){return $city->getCode();}, $originCities),
                'destinations' => array_map(function($city){return $city->getCode();}, $destinationCities),
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
     * @param LoadFlightsCommandState $state
     *
     * @return ArrayInput
     */
    private function getLoadFlightsCommandArguments(LoadFlightsCommandState $state): ArrayInput
    {
        $arguments = [
            'command'        => LoadFlightsCommand::getDefaultName(),
            '--origin'       => $state->getOrigin()->getCode(),
            '--destination'  => $state->getDestination()->getCode(),
            '--depart_month' => $state->getDepartMonthFirstDay()->format('Y-m-d'),
        ];

        $greetInput = new ArrayInput($arguments);

        return $greetInput;
    }

    /**
     * @param InputInterface $input
     *
     * @return bool|DateTime
     */
    private function getDepartureMonthFirstDay(InputInterface $input): ?DateTime
    {
        return $input->getArgument('depart_month_first_day') ? DateTime::createFromFormat('Y-m-d', $input->getArgument('depart_month_first_day')): null;
    }
}

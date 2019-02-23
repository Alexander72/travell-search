<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadFlightsCommand extends Command
{
    const API_URL_PATTERN = '%s/v2/prices/month-matrix';

    protected static $defaultName = 'LoadFlights';

    protected $apiToken;

    protected $apiUrl;

    protected $em;

    public function __construct(
        $apiUrl,
        $apiToken,
        EntityManagerInterface $em
    ) {
        parent::__construct();
        $this->apiToken = $apiToken;
        $this->apiUrl = $apiUrl;
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setDescription('Loads flights from Aviasales');
        $this->addOption('origin', 'o', InputOption::VALUE_OPTIONAL);
        $this->addOption('destination', 'd', InputOption::VALUE_OPTIONAL);
        $this->addOption('depart_month', 't', InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $cityRepository = $this->em->getRepository(City::class);
        $cityRepository->findAll();

        $client = new \GuzzleHttp\Client();
        $options = ['token' => $this->apiToken];
        if($input->getOption('origin'))
        {
            $options['origin'] = $input->getOption('origin');
        }
        if($input->getOption('destination'))
        {
            $options['destination'] = $input->getOption('destination');
        }
        if($input->getOption('depart_month'))
        {
            $options['month'] = $input->getOption('depart_month');
        }
        $url = sprintf(self::API_URL_PATTERN, $this->apiUrl);
        $response = $client->request('GET', $url, ['headers' => ['Accept-Encoding' => 'gzip, deflate'], 'query' => $options]);

        $response = $response->getBody();
        $response = \json_decode($response, true);


        foreach($response['data'] as $flightData)
        {
            $flight = new Route();
            $flight->setOrigin($cityRepository->find($flightData['origin']));
            $flight->setDestination($cityRepository->find($flightData['destination']));
            $flight->setCost($flightData['value']);
            $flight->setDepartureDay(\DateTime::createFromFormat('Y-m-d', $flightData['depart_date']) ?: null);
            $flight->setFoundAt(\DateTime::createFromFormat('Y-m-dTH:i:s', $flightData['found_at']) ?: null);
            $flight->setSavedAt(new \DateTime());
            $flight->setDuration($flightData['duration'] ?: null);

            $criteria = [
                'cost' => $flight->getCost(),
                'origin' => $flight->getOrigin(),
                'destination' => $flight->getDestination(),
                'departureDay' => $flight->getDepartureDay(),
            ];
            if(!$this->em->getRepository(Route::class)->findBy($criteria))
            {
                $this->em->merge($flight);
            }
        }

        $this->em->flush();
        $params = ' Origin: '.$input->getOption('origin');
        $params .= ' Destination: '.$input->getOption('destination');
        $params .= ' Departure month: '.$input->getOption('depart_month');
        $message = "Saved " . count($response['data']) . " flights." . $params;
        if(count($response['data']))
        {
            $io->success("Success! $message");
        }
        else
        {
            $io->warning($message);
        }
    }
}

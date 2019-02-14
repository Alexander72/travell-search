<?php

namespace App\Command;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCitiesCommand extends Command
{
    const API_URL_PATTERN = '%s/data/ru/cities.json?token=%s';

    protected static $defaultName = 'updateCities';

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
        $this->setDescription('Loads cities from Aviasales');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new \GuzzleHttp\Client();
        $url = sprintf(self::API_URL_PATTERN, $this->apiUrl, $this->apiToken);
        $response = $client->request('GET', $url);

        $cities = $response->getBody();
        $cities = \json_decode($cities, true);
        foreach($cities as $cityData)
        {
            $city = new City();
            $city->setName($cityData['name']);
            $city->setCode($cityData['code']);
            $city->setLat($cityData['coordinates']['lat']);
            $city->setLon($cityData['coordinates']['lon']);
            $city->setCountryCode($cityData['country_code']);

            $this->em->persist($city);
        }
        $this->em->flush();
    }
}

<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

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
        $io = new SymfonyStyle($input, $output);
        $this->em->getRepository(Country::class)->findAll();
        $this->em->getRepository(City::class)->findAll();

        $client = new \GuzzleHttp\Client();
        $url = sprintf(self::API_URL_PATTERN, $this->apiUrl, $this->apiToken);
        $response = $client->request('GET', $url, ['headers' => ['Accept-Encoding' => 'gzip, deflate']]);

        $cities = $response->getBody();
        $cities = \json_decode($cities, true);

        $validator = Validation::createValidator();
        $constraints = new Assert\Collection([
            'fields' => [
                    'name' => new Assert\NotBlank(),
                    'code' => new Assert\NotBlank(),
                    'coordinates' => [
                        new Assert\NotBlank(),
                        new Assert\Collection([
                            'lat' => [
                                new Assert\NotBlank(),
                                new Assert\Type('float'),
                            ],
                            'lon' => [
                                new Assert\NotBlank(),
                                new Assert\Type('float'),
                            ],
                        ])
                    ],
                ],
            'allowExtraFields' => true,
            ]);

        $incorrectCityCount = 0;
        foreach($cities as $cityData)
        {
            $violations = $validator->validate($cityData, $constraints);
            if(count($violations) === 0)
            {
                $city = new City();
                $city->setName($cityData['name']);
                $city->setCode($cityData['code']);
                $city->setLat($cityData['coordinates']['lat']);
                $city->setLon($cityData['coordinates']['lon']);
                $city->setCountry($this->em->getRepository(Country::class)->find($cityData['country_code']));

                $this->em->merge($city);
            }
            else
            {
                $incorrectCityCount++;
                //$io->warning('There are constraint violations on dataset: '.json_encode($cityData));
                /** @var ConstraintViolationInterface $violation */
                foreach($violations as $violation)
                {
                    //$io->writeln($violation->getPropertyPath().": ".$violation->getMessage());
                }
            }
        }

        $this->em->flush();
        $io->success("Success! Saved ".(count($cities) - $incorrectCityCount)." cities. Incorrect cities: $incorrectCityCount.");
    }
}

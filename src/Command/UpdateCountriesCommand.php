<?php

namespace App\Command;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCountriesCommand extends Command
{
    const API_URL_PATTERN = '%s/data/ru/countries.json?token=%s';

    protected static $defaultName = 'updateCountries';

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
        $this->setDescription('Loads countries from Aviasales');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->em->getRepository(Country::class)->findAll();

        $client = new \GuzzleHttp\Client();
        $url = sprintf(self::API_URL_PATTERN, $this->apiUrl, $this->apiToken);
        $response = $client->request('GET', $url, ['headers' => ['Accept-Encoding' => 'gzip, deflate']]);

        $countries = $response->getBody();
        $countries = \json_decode($countries, true);

        $validator = Validation::createValidator();
        $constraints = new Assert\Collection([
            'fields' => [
                'name' => new Assert\NotBlank(),
                'code' => new Assert\NotBlank(),
                'currency' => new Assert\Currency(),
            ],
            'allowExtraFields' => true,
        ]);

        $incorrectCityCount = 0;
        foreach($countries as $countryData)
        {
            $violations = $validator->validate($countryData, $constraints);
            if(count($violations) !== 0)
            {
                $incorrectCityCount++;
                continue;
            }

            $city = new Country();
            $city->setName($countryData['name']);
            $city->setCode($countryData['code']);
            $city->setCurrency($countryData['currency']);

            $this->em->merge($city);
        }

        $this->em->flush();
        $io->success("Success! Saved ".(count($countries) - $incorrectCityCount)." countries. Incorrect countries: $incorrectCityCount.");
    }
}

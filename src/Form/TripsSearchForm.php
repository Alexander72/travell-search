<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 12.03.19
 * Time: 23:35
 */

namespace App\Form;

use App\Entity\City;
use App\Entity\Country;
use App\Repository\CityRepository;
use App\Repository\CountryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;

class TripsSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder->add('startCountry', EntityType::class, $this->getCountryFieldOptions());
        $formBuilder->add('finishCountry', EntityType::class, $this->getCountryFieldOptions());
        $formBuilder->add('startCity', EntityType::class, $this->getCityFieldOptions());
        $formBuilder->add('finishCity', EntityType::class, $this->getCityFieldOptions());
        $formBuilder->add('startTime', DateType::class, ['widget' => 'single_text']);
        $formBuilder->add('finishTime', DateType::class, [
            'widget' => 'single_text',
            'constraints' => [
                new Assert\GreaterThan([
                    'propertyPath' => 'parent.all[startTime].data',
                ]),
            ]
        ]);
        $formBuilder->add('maxPrice', IntegerType::class, [
            'constraints' => [
                new Assert\LessThan(100000),
            ]
        ]);
        $formBuilder->add('maxChanges', IntegerType::class, [
            'constraints' => [
                new Assert\LessThan(10),
            ]
        ]);
        $formBuilder->add('search', SubmitType::class);
        return $formBuilder->getForm();
    }

    /**
     * @return array
     */
    private function getCityFieldOptions(): array
    {
        return [
            'class' => City::class,
            'choice_label' => 'name',
            'help' => 'Field not required when country is specified.',
            'query_builder' => function(CityRepository $cityRepository) {
                return $cityRepository->getLargeEuropeCitiesQueryBuilder();
            }
        ];
    }

    /**
     * @return array
     */
    private function getCountryFieldOptions(): array
    {
        return [
            'class' => Country::class,
            'choice_label' => 'name',
            'required' => false,
            'placeholder' => 'Select country',
            'query_builder' => function(CountryRepository $countryRepository) {
                return $countryRepository->getEuropeCountriesQueryBuilder();
            }
        ];
    }
}
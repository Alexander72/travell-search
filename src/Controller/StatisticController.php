<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 19.03.19
 * Time: 0:27
 */

namespace App\Controller;

use App\Repository\LoadFlightsCommandStateRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StatisticController extends AbstractController
{
    private $stateRepository;

    public function __construct(
        LoadFlightsCommandStateRepository $stateRepository
    ) {
        $this->stateRepository = $stateRepository;
    }


    /**
     * @Route("/admin/statistic", name="statistic")
     */
    public function index()
    {
        $data = $this->getStatData();

        return $this->render('admin/statistic.twig', $data);
    }

    /**
     * @Route("/admin/statistic/api", name="statisticApi")
     */
    public function statisticApiData()
    {
        $data = $this->getStatData();

        return new JsonResponse($data);
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getStatData(): array
    {
        $lastUnfinishedState = $this->stateRepository->getLastState(false);

        $data = [
            'lastUnfinishedState' => [
                'memoryUsage' => $lastUnfinishedState->getMemoryUsage(),
                'percent' => $lastUnfinishedState->getPercent(),
            ],
        ];

        return $data;
    }
}
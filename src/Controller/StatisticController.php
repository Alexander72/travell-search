<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 19.03.19
 * Time: 0:27
 */

namespace App\Controller;

use App\Repository\LoadFlightsCommandStateRepository;
use App\Repository\RouteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StatisticController extends AbstractController
{
    private $stateRepository;

    private $routeRepository;

    public function __construct(
        LoadFlightsCommandStateRepository $stateRepository,
        RouteRepository $routeRepository
    ) {
        $this->stateRepository = $stateRepository;
        $this->routeRepository = $routeRepository;
    }


    /**
     * @Route("/admin/statistic", name="statistic")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $this->getStatData();
        $data['states'] = $this->stateRepository->findBy([], ['id' => 'DESC'], 15);

        return $this->render('admin/statistic.twig', $data);
    }

    /**
     * @Route("/admin/statistic/api", name="statisticApi")
     */
    public function statisticApiData()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $this->getStatData();

        return new JsonResponse($data);
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getStatData(): array
    {
        $data = [];

        $lastUnfinishedState = $this->stateRepository->getLastState(false);
        $data['lastUnfinishedState'] = [
            'memoryUsage' => $lastUnfinishedState ? $lastUnfinishedState->getMemoryUsage(): 0,
            'percent' => $lastUnfinishedState ? $lastUnfinishedState->getPercent() : 0,
        ];

        $data['routesCount'] = $this->routeRepository->count([]);

        return $data;
    }
}
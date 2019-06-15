<?php


namespace App\Controller;


use App\Repository\RouteRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StatController extends AbstractController
{
    /**
     * @var RouteRepository
     */
    private $routeRepository;

    public function __construct(RouteRepository $routeRepository)
    {
        $this->routeRepository = $routeRepository;
    }

    /**
     * @Route("/stat", name="stat")
     */
    public function index()
    {
        list($minPrice, $maxPrice,) = $this->routeRepository->getMinMaxPricesForStatMap();
        $data = [
            'routes' => array_map(function($row) use ($minPrice, $maxPrice) {
                $row['price'] = ($row['price'] - $minPrice) / ($maxPrice - $minPrice);
                return $row;
            }, $this->routeRepository->getPricesForStatMap()),
        ];

        return $this->render('stat/stat.twig', $data);
    }
}
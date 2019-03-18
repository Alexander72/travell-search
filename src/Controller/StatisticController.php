<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 19.03.19
 * Time: 0:27
 */

namespace App\Controller;

use App\Repository\LoadFlightsCommandStateRepository;
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
        $lastUnfinishedState = $this->stateRepository->getLastState(false);
        return $this->render('admin/statistic.twig');
    }
}
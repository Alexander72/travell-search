<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 19.03.19
 * Time: 0:27
 */

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StatisticController extends AbstractController
{
    /**
     * @Route("/admin/statistic", name="statistic")
     */
    public function index()
    {
        return $this->render('admin/statistic.twig');
    }
}
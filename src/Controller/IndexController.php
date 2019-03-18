<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 19.03.19
 * Time: 0:43
 */

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return $this->render('index.twig');
    }
}
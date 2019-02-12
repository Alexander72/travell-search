<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 13.02.19
 * Time: 1:01
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return new Response('Success!');
    }
}
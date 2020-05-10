<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api")
 */
class TestController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index() {
        return $this->json("test");
    }
}
<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route(path="/api_login")
     */
    public function login() {
        return new JsonResponse(["message" => "Please login"], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @Route(path="/{req}", requirements={"req"="^(?!test|api).*"})
     * @return Response
     */
    public function index() {
        return $this->render('index.html.twig');
    }
}
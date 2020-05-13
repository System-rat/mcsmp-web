<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserAPIKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/user")
 */
class UserController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/get_info")
     */
    public function getInfo()
    {
        $user = $this->getUser();
        if ($user instanceof User) {
           $data = [
               "username" => $user->getUsername(),
               "display_name" => $user->getDisplayName()
           ];
           return new JsonResponse($data);
        }
        return new JsonResponse(["message" => "Invalid user"], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @Route(path="/logout")
     * @param Request $request
     */
    public function logout(Request $request) {
        $token = $request->headers->get("X-AUTH-TOKEN");
        $apiKey = $this->em->getRepository(UserAPIKey::class)->findOneBy(["api_key" => $token]);
        if ($apiKey) {
            $this->em->remove($apiKey);
            $this->em->flush();
            return new Response("");
        }
        return new Response("", Response::HTTP_BAD_REQUEST);
    }
}

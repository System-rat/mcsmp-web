<?php


namespace App\Controller;


use App\Util\ConnectorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @Route(path="/api/server")
 */
class TestController extends AbstractController
{
    private ConnectorInterface $connector;

    public function __construct(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @Route("/available_servers")
     */
    public function availableServers(Request $request) {
        $options = [];
        $name = $request->query->get("name");
        if ($name !== null) {
            $options["query"] = [
                "name" => $name
            ];
        }
        try {
            $response = $this->connector->request("GET", "/available_servers", $options);
            if ($response->getStatusCode() === 200) {
                return new Response($response->getContent());
            } else {
                return new JsonResponse(
                    ["message" => "Error with connector: most likely unauthorized"],
                    Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse(["message" => "Error with connector."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/get_logs/{name}")
     */
    public function getLogs(string $name) {
        $response = $this->connector->request("GET", "/get_log", [
            "query" => [
                "name" => $name
            ]
        ]);
        return new Response($response->getContent(false), $response->getStatusCode());
    }
}
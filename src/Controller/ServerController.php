<?php


namespace App\Controller;


use App\Repository\ConnectorRepository;
use App\Util\ConnectorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @Route(path="/api/server")
 */
class ServerController extends AbstractController
{
    private ConnectorRepository $connectorRepository;

    public function __construct(ConnectorRepository $connectorRepository)
    {
        $this->connectorRepository = $connectorRepository;
    }

    /**
     * @Route("/available_servers/{connector}")
     */
    public function availableServers(Request $request, int $connector) {
        $con = $this->connectorRepository->find($connector);
        if (!$con) {
            return new JsonResponse([
                "message" => "Connector does not exist"
            ], Response::HTTP_BAD_REQUEST);
        }
        $options = [];
        $name = $request->query->get("name");
        if ($name !== null) {
            $options["query"] = [
                "name" => $name
            ];
        }
        try {
            $response = $con->sendRequest("GET", "/available_servers", $options);
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
     * @Route("/get_logs/{connector}/{name}")
     */
    public function getLogs(string $name, int $connector) {
        $con = $this->connectorRepository->find($connector);
        if (!$con) {
            return new JsonResponse([
                "message" => "Connector does not exist"
            ], Response::HTTP_BAD_REQUEST);
        }
        $response = $con->sendRequest("GET", "/get_log", [
            "query" => [
                "name" => $name
            ]
        ]);
        return new Response($response->getContent(false), $response->getStatusCode());
    }

    public function startServer(string $name, int $connector) {

    }
}
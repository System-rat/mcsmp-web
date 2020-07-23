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
        $this->connectorNotFound = new JsonResponse([
            "message" => "Connector does not exist"
        ], Response::HTTP_BAD_REQUEST);
    }

    private JsonResponse $connectorNotFound;
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

    /**
     * @Route("/get_properties/{connector}/{name}")
     */
    public function getProperties(string $name, int $connector) {
        $con = $this->connectorRepository->find($connector);
        if (!$con) {
            return $this->connectorNotFound;
        }
        
        try {
            $response = $con->sendRequest("GET", "/get_properties/${name}");
            return new Response($response->getContent(false), $response->getStatusCode());
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse([
                "message" => "Error with connector"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route(path="/update_properties/{connector}/{name}", methods={"PATCH"})
     */
    public function updateProperties(Request $request, string $name, int $connector) {
        $con = $this->connectorRepository->find($connector);
        if (!$con) {
            return $this->connectorNotFound;
        }

        $properties = $request->request->get("properties");

        try {
            $response = $con->sendRequest("POST", "/set_properties/${name}", [
                "body" => json_encode([
                    "properties" => json_decode($properties)
                ])
            ]);
            return new Response($response->getContent(false), $response->getStatusCode());
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse([
                "message" => "Error with connector"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route(path="/download_latest/{connector}/{name}", methods={"POST"})
     */
    public function downloadLatest(Request $request, string $name, string $connector) {
        $con = $this->connectorRepository->find($connector);
        if (!$con) {
            return $this->connectorNotFound;
        }

        $is_snapshot = $request->request->get("is_snapshot");

        try {
            $response = $con->sendRequest("POST", "/set_latest_version/${name}", [
                "body" => [
                    "is_snapshot" => $is_snapshot
                ]
            ]);
            if ($response->getStatusCode() === Response::HTTP_OK) {
                $responseData = json_decode($response->getContent(false));
                return new JsonResponse([
                    "is_snapshot" => $responseData->new_state->instance->version->is_snapshot,
                    "version" => $responseData->new_state->instance->version->id
                ], Response::HTTP_OK);
            } else {
                return new JsonResponse([
                    "message" => "Error with request"
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse([
                "message" => "Error with connector"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function startServer(string $name, int $connector) {

    }

}
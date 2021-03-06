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
     * @Route(path="/create_server/{connector}", methods={"POST"})
     */
    public function createServer(Request $request, int $connector) {
        $con = $this->connectorRepository->find($connector);
        if (!$con) {
            return new JsonResponse([
                "message" => "Connector does not exist"
            ], Response::HTTP_BAD_REQUEST);
        }
        $serverName = $request->request->get("server_name");
        $serverVersion = $request->request->get("server_version");
        if ($serverName === null) {
            return new JsonResponse([
                "message" => "Required params not set"
            ], Response::HTTP_BAD_REQUEST);
        }
        $body = [
            "server_name" => $serverName
        ];
        if ($serverVersion !== null) {
            $body["server_version"] = $serverVersion;
        }

        try {
            $response = $con->sendRequest("POST", "/create_server", [
                "body" => json_encode($body)
            ]);
            return new Response($response->getContent(false), $response->getStatusCode());
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse([
                "message" => "Error with connector"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route(path="/delete_server/{connector}/{name}", methods={"DELETE"})
     */
    public function deleteServer(int $connector, string $name) {
        $con = $this->connectorRepository->find($connector);
        if (!$con) {
            return new JsonResponse([
                "message" => "Connector does not exist"
            ], Response::HTTP_BAD_REQUEST);
        }
        try {
            $response = $con->sendRequest("POST", "/delete_server/$name");
            return new Response($response->getContent(false), $response->getStatusCode());
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse([
                "message" => "Error with connector"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            if ($response->getStatusCode() === Response::HTTP_INTERNAL_SERVER_ERROR) {
                return new JsonResponse([
                    "message" => "Incorrect property values"
                ], Response::HTTP_BAD_REQUEST);
            }
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
                    "version" => $responseData->new_state->instance->version->id,
                    "running" => $responseData->new_state->running
                ], Response::HTTP_OK);
            } else {
                return new JsonResponse([
                    "message" => "Error with download"
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse([
                "message" => "Error with connector"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route(path="/download_version/{connector}/{name}", methods={"POST"})
     */
    public function downloadVersion(Request $request, string $name, string $connector) {
        $con = $this->connectorRepository->find($connector);
        if (!$con) {
            return $this->connectorNotFound;
        }

        $version = $request->request->get("version");

        try {
            $response = $con->sendRequest("POST", "/set_version/${name}", [
                "body" => [
                    "version" => $version
                ]
            ]);
            if ($response->getStatusCode() === Response::HTTP_OK) {
                $responseData = json_decode($response->getContent(false));
                return new JsonResponse([
                    "is_snapshot" => $responseData->new_state->instance->version->is_snapshot,
                    "version" => $responseData->new_state->instance->version->id,
                    "running" => $responseData->new_state->running
                ], Response::HTTP_OK);
            } else {
                return new JsonResponse([
                    "message" => "Error with download"
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse([
                "message" => "Error with connector"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route(path="/stop_server/{connector}/{name}", methods={"POST"})
     */
    public function stopServer(string $name, string $connector) {
        $con = $this->connectorRepository->find($connector);
        if (!$con) {
            return $this->connectorNotFound;
        }

        try {
            $response = $con->sendRequest("POST", "/stop_server/${name}");
            if ($response->getStatusCode() === Response::HTTP_OK) {
                $responseData = json_decode($response->getContent(false));
                return new JsonResponse([
                    "is_snapshot" => $responseData->new_state->instance->version->is_snapshot,
                    "version" => $responseData->new_state->instance->version->id,
                    "running" => $responseData->new_state->running
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

    /**
     * @Route(path="/start_server/{connector}/{name}", methods={"POST"})
     */
    public function startServer(string $name, string $connector) {
        $con = $this->connectorRepository->find($connector);
        if (!$con) {
            return $this->connectorNotFound;
        }

        try {
            $response = $con->sendRequest("POST", "/start_server/${name}");
            if ($response->getStatusCode() === Response::HTTP_OK) {
                $responseData = json_decode($response->getContent(false));
                return new JsonResponse([
                    "is_snapshot" => $responseData->new_state->instance->version->is_snapshot,
                    "version" => $responseData->new_state->instance->version->id,
                    "running" => $responseData->new_state->running
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
}
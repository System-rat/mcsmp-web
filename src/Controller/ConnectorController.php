<?php

namespace App\Controller;

use App\Entity\Connector;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class ConnectorController
 * @package App\Controller
 * @Route(path="/api/connector")
 */
class ConnectorController extends AbstractController
{
    private EntityManagerInterface $em;
    private ObjectRepository $connectors;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->connectors = $this->em->getRepository(Connector::class);
    }

    /**
     * @Route("/get_connectors")
     */
    public function getConnectors() {
        $connectors = $this->connectors->findAll();
        $connectorResponse = ["connectors"=>[]];

        foreach ($connectors as $connector) {
            $connectorArray = [
                "id" => $connector->getId(),
                "host" => $connector->getHost(),
                "port" => $connector->getPort(),
                "token" => $connector->getToken(),
                "subDirectory" => $connector->getSubDirectory()
            ];
            $connectorArray["status"] = $this->getConnectorStatus($connector);
            $connectorResponse["connectors"][] = $connectorArray;
        }

        return new JsonResponse($connectorResponse);
    }

    /**
     * @Route(path="/create_connector", methods={"POST"})
     */
    public function createConnector(Request $request) {
        if (!$request->request->has("host") && !$request->request->get("host") === "") {
            return new JsonResponse(["message" => "Missing required fields."], Response::HTTP_BAD_REQUEST);
        }

        $host = $request->request->get("host");
        $port = $request->request->get("port", 1337);
        $token = $request->request->get("token");
        $subDirectory = $request->request->get("sub_directory");

        $connector = new Connector();
        $connector->setHost($host);
        $connector->setPort($port);
        $connector->setToken($token);
        $connector->setSubDirectory($subDirectory);

        $this->em->persist($connector);
        $this->em->flush();

        return new JsonResponse([
            "message" => "Connector created",
            "result" => $connector->getId(),
            "status" => $this->getConnectorStatus($connector)
        ]);
    }

    /**
     * @Route(path="/update_connector/{id}", methods={"PATCH"})
     */
    public function updateConnector(Request $request, int $id) {
        $connector = $this->connectors->find($id);
        if (!$connector) {
            return new JsonResponse(["message" => "Connector does not exist"], Response::HTTP_BAD_REQUEST);
        }

        $host = $request->request->get("host");
        $port = $request->request->get("port");
        $token = $request->request->get("token");
        $subDirectory = $request->request->get("sub_directory");

        if ($host && $host !== "") {
            $connector->setHost($host);
        }

        if ($port) {
            $connector->setPort($port);
        }

        if ($token && $token !== "") {
            $connector->setToken($token);
        } else if ($token && $token === "") {
            $connector->setToken(null);
        }

        if ($subDirectory && $subDirectory !== "") {
            $connector->setSubDirectory($subDirectory);
        } else if ($subDirectory && $subDirectory === "") {
            $connector->setSubDirectory(null);
        }

        $this->em->flush();
        return new JsonResponse([
            "message" => "Updated connector",
            "result" => $connector,
            "status" => $this->getConnectorStatus($connector)
        ]);
    }

    /**
     * @Route(path="/delete_connector/{id}", methods={"DELETE"})
     */
    public function deleteConnector(int $id) {
        $connector = $this->connectors->find($id);
        if (!$connector) {
            return new JsonResponse(["message" => "Connector does not exist"], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($connector);
        $this->em->flush();
        return new JsonResponse(["message" => "Connector deleted"]);
    }

    private function getConnectorStatus(Connector $connector) {
        try {
            if ($connector->sendRequest("GET", "/heartbeat", ["timeout" => 2.5])->getStatusCode() === 200) {
                return "alive";
            } else {
                return "probably alive i really don't know lmao";
            }
        } catch (TransportExceptionInterface $e) {
            return "dead";
        }
    }
}

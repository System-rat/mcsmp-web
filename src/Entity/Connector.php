<?php

namespace App\Entity;

use App\Repository\ConnectorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @ORM\Entity(repositoryClass=ConnectorRepository::class)
 */
class Connector
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $host;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $subDirectory;

    /**
     * @ORM\Column(type="integer")
     */
    private int $port;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $token;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubDirectory(): string
    {
        return $this->subDirectory;
    }

    /**
     * @param string $subDirectory
     */
    public function setSubDirectory(string $subDirectory): void
    {
        $this->subDirectory = $subDirectory;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function sendRequest(string $method, string $url, array $options = []) {
        if (!isset($options["headers"])) {
            $options["headers"] = [
                "Authorization" => $this->token
            ];
        } else {
            $options["headers"]["Authorization"] = $this->token;
        }
        $finalUrl = $this->host . ":" . $this->port;
        if ($this->subDirectory) {
            $finalUrl = $finalUrl . "/" . $this->subDirectory;
        }
        return HttpClient::create()->request($method, $finalUrl . $url, $options);
    }
}

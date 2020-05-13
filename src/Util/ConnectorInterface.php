<?php


namespace App\Util;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ConnectorInterface
{
    private HttpClientInterface $connector;
    private string $secret;
    private ?string $subdir;

    public function __construct($connectorUrl, $connectorSecret, $connectorSubdir)
    {
        $this->connector = HttpClient::createForBaseUri($connectorUrl);
        $this->secret = $connectorSecret;
        $this->subdir = $connectorSubdir;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface {
        if (!isset($options["headers"])) {
            $options["headers"] = [
                "Authorization" => $this->secret
            ];
        } else {
            $options["headers"]["Authorization"] = $this->secret;
        }
        return $this->connector->request($method, $this->subdir.$url, $options);
    }
}
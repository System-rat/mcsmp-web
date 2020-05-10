<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
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
    private string $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $authenticator;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $display_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $email;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $date_created;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $is_active = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $mojang_client_id;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $is_mojang_account = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserAPIKey", mappedBy="user_id")
     * @var UserAPIKey[]
     */
    private $api_keys = [];

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = "ROLE_USER";

        return $roles;
    }

    public function eraseCredentials()
    {

    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return UserAPIKey[]
     */
    public function getApiKeys(): array
    {
        return $this->api_keys;
    }

    /**
     * @param UserAPIKey[] $api_keys
     */
    public function setApiKeys(array $api_keys): void
    {
        $this->api_keys = $api_keys;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getAuthenticator(): ?string
    {
        return $this->authenticator;
    }

    public function getSalt()
    {
        return "";
    }

    public function setAuthenticator(string $authenticator): self
    {
        $this->authenticator = $authenticator;

        return $this;
    }

    public function getPassword()
    {
        return $this->getAuthenticator();
    }

    public function getDisplayName(): ?string
    {
        return $this->display_name;
    }

    public function setDisplayName(?string $display_name): self
    {
        $this->display_name = $display_name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDateCreated(): ?\DateTime
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTime $date_created): self
    {
        $this->date_created = $date_created;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getMojangClientId(): ?string
    {
        return $this->mojang_client_id;
    }

    public function setMojangClientId(?string $mojang_client_id): self
    {
        $this->mojang_client_id = $mojang_client_id;

        return $this;
    }

    public function getIsMojangAccount(): ?bool
    {
        return $this->is_mojang_account;
    }

    public function setIsMojangAccount(bool $is_mojang_account): self
    {
        $this->is_mojang_account = $is_mojang_account;

        return $this;
    }

    public function __construct()
    {
        $this->date_created = new \DateTime("now");
    }
}

<?php


namespace App\Security;


use App\Entity\User;
use App\Entity\UserAPIKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiLoginAuthenticator extends AbstractGuardAuthenticator
{
    private EntityManagerInterface $em;

    private const LOGIN_PATH = "/api_login";

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @inheritDoc
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            "message" => "Invalid login."
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request)
    {
        return /*$request->attributes->get("_route") === self::LOGIN_PATH
            && */$request->isMethod("POST")
            && $request->request->has("username")
            && $request->request->has("authenticator");
    }

    /**
     * @inheritDoc
     */
    public function getCredentials(Request $request)
    {
        return [
            "username" => $request->request->get("username"),
            "authenticator" => $request->request->get("authenticator")
        ];
    }

    /**
     * @inheritDoc
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => $credentials["username"]]);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException("Wrong username or password");
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => $user->getUsername()]);
        if ($user->getIsMojangAccount()) {
            // TODO: call Mojang api
            return false;
        } else {
            return strtolower($user->getAuthenticator()) === hash("sha512", $credentials["authenticator"]);
        }
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            "message" => "Login failure",
            "error" => $exception->getMessage(),
            "hash" => hash("sha512", $request->request->get("authenticator"))
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        $api_token = hash("sha512", (new \DateTime())->format("YYMMDDhhmmss"));
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => $token->getUsername()]);
        $api_key = new UserAPIKey();
        $api_key->setApiKey($api_token);
        $api_key->setUser($user);
        $expiration_date = (new \DateTime())->add(new \DateInterval("P30D"));
        $api_key->setExpiresAt($expiration_date);
        $this->em->persist($api_key);
        $this->em->flush();

        return new JsonResponse(["message" => "Login success", "key" => $api_token]);
    }

    /**
     * @inheritDoc
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
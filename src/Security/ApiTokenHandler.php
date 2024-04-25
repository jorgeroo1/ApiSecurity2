<?php

namespace App\Security;

use App\Repository\ApiTokenRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(private ApiTokenRepository $repository)
    {
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        //comprobamos si el token existe en la base de datos
        $token = $this->repository->findOneBy(['token' => $accessToken]);
        if (!$token){
            throw new BadCredentialsException();
        }
        if (!$token->isValid()){
            throw new BadCredentialsException();
        }
        //estamos devolviendo un UserBadge porque está interfaz nos lo exige y el primer metodo
        // me devuelve un objeto User y el segundo el email
        return new UserBadge($token->getOwnedBy()->getUserIdentifier());
        // TODO: Implement getUserBadgeFrom() method.
    }
}
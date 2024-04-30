<?php

namespace App\Security\Voter;

use App\Entity\DragonTreasure;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;

class DragonTreasureVoter extends Voter
{
    public const EDIT = 'EDIT';

    public function __construct(private Security $security)
    {
    }
    protected function supports(string $attribute, mixed $subject): bool
    {
        // https://symfony.com/doc/current/security/voters.html
        //le estamos diciendo que el segundo parametro que le pasemos a este metodo
        // que sera el isgranted debe ser una instancia de DragonTreasure
        //si aqui devolvemos true (tiene acceso) se llamará a voteOnAttribute
        return in_array($attribute, [self::EDIT])
            && $subject instanceof DragonTreasure;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        //comprobamos de nuevo que el subject es de tipo DragonTreasure porque este metodo deberia llamarse
        //cuando el metodo supports devuelve true
//        assert($subject instanceof DragonTreasure);

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                //el servicio security sirve para saber si un usuario tiene un rol en especifico
                if (!$this->security->isGranted('ROLE_TREASURE_EDIT')) {
                    return false;
                }
                //si el usuario del tesoro es igual al usuario registrado devuelves true y das permiso para realizar esa operacion
                if ($subject->getOwner() === $user) {
                    return true;
                }
                // logic to determine if the user can EDIT
                // return true or false
                break;
        }

        return false;
    }
}

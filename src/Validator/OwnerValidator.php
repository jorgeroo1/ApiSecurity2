<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OwnerValidator extends ConstraintValidator
{
    public function __construct(private Security $security)
    {
    }
    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof Owner);

        if (null === $value || '' === $value) {
            return;
        }
        assert($value instanceof User);
        //obtenemos el usuario del tesoro
        $user = $this->security->getUser();
        //si el usuario es admin salir de la funcion para que se pueda assignar cualquier owner
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }
        if (!$user) {
            throw new \LogicException('OwnerValidator should only be used when a user is logged in.');
        }
        //si el usuario actual, el value es el User, no es el propietario me saldra el mensaje de error
        if ($value !== $user) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

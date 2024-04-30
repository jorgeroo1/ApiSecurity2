<?php

namespace App\Validator;

use App\Entity\DragonTreasure;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TreasuresAllowedOwnerChangeValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof TreasuresAllowedOwnerChange);
        if (null === $value || '' === $value) {
            return;
        }
        // meant to be used above a Collection field
        assert($value instanceof Collection);
        //para obtener el objeto registrado pero no sus datos

        $unitOfWork = $this->entityManager->getUnitOfWork();

        foreach ($value as $dragonTreasure) {
            assert($dragonTreasure instanceof DragonTreasure);
            //pillamos los datos originales que tiene nuestro tesoro
            $originalData = $unitOfWork->getOriginalEntityData($dragonTreasure);
            //capturamos el dueño original
            $originalOwnerId = $originalData['owner_id'];
            //capturamos el id del dueño que está intentando hacer la operacion
            $newOwnerId = $dragonTreasure->getOwner()->getId();
            //si son iguales no hay problema porque el dueño es el mismo
            if (!$originalOwnerId || $originalOwnerId === $newOwnerId) {
                return;
            }
            // the owner is being changed
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

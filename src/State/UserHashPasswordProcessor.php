<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

//para añadir nuevas funcionalidad sin modificar el objeto ya existente
#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class UserHashPasswordProcessor implements ProcessorInterface
{
    public function __construct(private ProcessorInterface $innerProcessor, private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    //antes de insertarse en la base de datos pasara por aqui
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($data instanceof User && $data->getPlainPassword()){
            //en este caso el data seria el User, estamos diciendo que la contraseña va a ser la contraseña plana pero hasheada
            $data->setPassword($this->userPasswordHasher->hashPassword($data, $data->getPlainPassword()));
        }
        //esto asegura que se ejecute el contenido adicional del metodo sin modificar el comportamiento del objeto original
        $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}

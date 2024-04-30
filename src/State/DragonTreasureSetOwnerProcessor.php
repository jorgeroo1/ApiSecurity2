<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class DragonTreasureSetOwnerProcessor implements ProcessorInterface
{
    public function __construct(private ProcessorInterface $innerProcessor, private Security $security)
    {
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        //si el owner de un tesoro es nulo, le asignamos el usuario como propietario de manera automática antes de que se
        //haga un persist en la base de datos
        if ($data instanceof DragonTreasure && $data->getOwner() === null && $this->security->getUser()) {
            $data->setOwner($this->security->getUser());
        }
        // Handle the state

        $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}

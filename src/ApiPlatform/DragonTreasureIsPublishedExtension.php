<?php

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\DragonTreasure;
use Doctrine\ORM\QueryBuilder;

class DragonTreasureIsPublishedExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if (DragonTreasure::class !== $resourceClass) {
            return;
        }
        //conseguir el alias de la query, lo proporciona symfony de manera interna
        $rootAlias = $queryBuilder->getRootAliases()[0];
        //que solo me pille los tesoros que tengan isPublished en true
        $queryBuilder->andWhere(sprintf('%s.isPublished = :isPublished', $rootAlias))
            ->setParameter('isPublished', true);
    }
}

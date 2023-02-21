<?php

namespace App\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class CaseDeletedFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (\App\Entity\CaseEntity::class != $targetEntity->getReflectionClass()->name) {
            return '';
        }

        return sprintf('%s.soft_deleted = false', $targetTableAlias);
    }
}

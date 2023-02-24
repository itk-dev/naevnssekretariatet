<?php

namespace App\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class CaseDeletedFilter extends SQLFilter
{
    /**
     * @return string the constraint SQL if there is available, empty string otherwise
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ('App\Entity\CaseEntity' != $targetEntity->getReflectionClass()->name) {
            return '';
        }

        return sprintf('%s.soft_deleted = false', $targetTableAlias);
    }
}

<?php

namespace App\Repository;

use App\Entity\MailTemplateMacro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MailTemplateMacro|null find($id, $lockMode = null, $lockVersion = null)
 * @method MailTemplateMacro|null findOneBy(array $criteria, array $orderBy = null)
 * @method MailTemplateMacro[]    findAll()
 * @method MailTemplateMacro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailTemplateMacroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailTemplateMacro::class);
    }

    /**
     * Find macros by template type.
     */
    public function findByTemplateType(string $templateType, array $criteria = [], array $orderBy = null, $limit = null, $offset = null): array
    {
        // This is a lazy, but effective, way to filter out the macros having no attached template type or one matching the requested type.
        $macros = $this->findAll();
        $macros = array_filter($macros, static fn (MailTemplateMacro $macro) => empty($macro->getTemplateTypes()) || in_array($templateType, $macro->getTemplateTypes(), true));
        $criteria['id'] = array_map(static fn (MailTemplateMacro $macro) => $macro->getId()->toBinary(), $macros);
        $macros = $this->findBy($criteria, $orderBy, $limit, $offset);

        // Sort macros by number of template types to make sure that macros with a template type matching the requested type comes first.
        usort($macros, static fn (MailTemplateMacro $m0, MailTemplateMacro $m1) => -(count($m0->getTemplateTypes()) <=> count($m1->getTemplateTypes())));

        return $macros;
    }
}

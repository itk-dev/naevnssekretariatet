<?php

namespace App\Controller\Admin;

use App\Entity\BoardRole;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BoardRoleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BoardRole::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Add board role')
            ->setEntityLabelInSingular('Board role')
            ->setEntityLabelInPlural('Board roles')
            ->setSearchFields(['board'])
            ->setDefaultSort(['board' => 'ASC'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'Title');
        yield AssociationField::new('board', 'Board')
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                return $queryBuilder->orderBy('entity.name', 'ASC');
            })
        ;
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\BoardMember;
use App\Entity\BoardRole;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Translation\TranslatorInterface;

class BoardMemberCrudController extends AbstractCrudController
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return BoardMember::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Add boardmember')
            ->setEntityLabelInSingular('Boardmember')
            ->setEntityLabelInPlural('Boardmembers')
            ->setSearchFields(['name'])
            ->setDefaultSort(['name' => 'ASC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name');
        yield TextField::new('cpr', 'CPR')
            // Hide the CPR a bit.
            ->onlyOnForms()
        ;
        yield AssociationField::new('boardRoles', 'BoardRole')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                return $queryBuilder->orderBy('entity.title', 'ASC');
            })
            ->formatValue(function ($value, BoardMember $member) {
                $roles = $member->getBoardRoles()->map(function (BoardRole $boardRole) {
                    return $boardRole->__toString();
                });

                return implode(', ', $roles->getValues());
            })
            ->setHelp($this->translator->trans('Remember that a boardmember may only have one role per board. ', [], 'admin'))
        ;
    }
}

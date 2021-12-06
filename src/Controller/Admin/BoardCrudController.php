<?php

namespace App\Controller\Admin;

use App\Entity\Board;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BoardCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Board::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN')
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Add board')
            ->setEntityLabelInSingular('Board')
            ->setEntityLabelInPlural('Boards')
            ->setSearchFields(['name', 'municipality.name'])
            ->setDefaultSort(['name' => 'ASC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name');

        yield AssociationField::new('municipality', 'Municipality');

        yield ChoiceField::new('caseFormType', 'Case Form Type')
            ->setChoices([
                'Resident complaint form' => 'ResidentComplaintBoardCaseType',
                'Rent board complaint form' => 'RentBoardCaseType',
                'Fence review form' => 'FenceReviewCaseType',
            ])
            ->setRequired('true')
        ;

        yield IntegerField::new('defaultDeadline', 'Default Deadline(days)');
        yield TextareaField::new('complainantPartyTypes', 'Complainant party types');
        yield TextareaField::new('counterPartyTypes', 'Counter party types');
        yield TextareaField::new('statuses', 'Statuses');
    }
}

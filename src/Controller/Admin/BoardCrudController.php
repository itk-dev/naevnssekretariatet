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
use Symfony\Contracts\Translation\TranslatorInterface;

class BoardCrudController extends AbstractCrudController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
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

        yield IntegerField::new('hearingResponseDeadline', 'Hearing response deadline(days)');
        yield IntegerField::new('finishHearingDeadlineDefault', 'Finish hearing deadline(days)');
        yield IntegerField::new('finishProcessingDeadlineDefault', 'Finish processing case deadline(days)');
        yield TextareaField::new('complainantPartyTypes', 'Complainant party types')
            ->setHelp($this->translator->trans('Configuration of complainant types. Each line will be treated as a type of which the first will be the one used for sorting purposes.'))
        ;
        yield TextareaField::new('counterPartyTypes', 'Counter party types')
            ->setHelp($this->translator->trans('Configuration of counterpart types. Each line will be treated as a type of which the first will be the one used for sorting purposes.'))
        ;
        yield TextareaField::new('statuses', 'Statuses')
            ->setHelp('Configuration of case statuses. Each line will be treated as a status and the order of case statuses will be reflected by the order of these.')
        ;
    }
}

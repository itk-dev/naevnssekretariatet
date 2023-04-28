<?php

namespace App\Controller\Admin;

use App\Entity\Board;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Translation\TranslatableMessage;
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
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
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

        yield TextField::new('id', 'Id')
            ->onlyOnDetail()
        ;

        yield AssociationField::new('municipality', 'Municipality')
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                return $queryBuilder->orderBy('entity.name', 'ASC');
            })
        ;

        yield EmailField::new('email', 'Email')
            ->hideOnIndex()
        ;

        yield UrlField::new('url', 'Url')
            ->hideOnIndex()
        ;

        yield ChoiceField::new('caseFormType', 'Case Form Type')
            ->setChoices([
                'Resident complaint form' => 'ResidentComplaintBoardCaseType',
                'Rent board complaint form' => 'RentBoardCaseType',
                'Fence review form' => 'FenceReviewCaseType',
            ])
            ->setRequired('true')
            ->hideOnIndex()
        ;

        yield IntegerField::new('hearingResponseDeadline', 'Hearing response deadline (days)');
        yield IntegerField::new('finishHearingDeadlineDefault', 'Finish hearing deadline (days)');
        yield IntegerField::new('finishProcessingDeadlineDefault', 'Finish processing case deadline (days)');
        yield TextareaField::new('partyTypes', 'Party types')
            ->setHelp($this->translator->trans('List of party types (one per line). The first party type can be used for sorting cases.',
                [], 'admin'))
        ;
        yield TextareaField::new('counterpartyTypes', 'Counter party types')
            ->setHelp($this->translator->trans('List of counterparty types (one per line). The first party type can be used for sorting cases.',
                [], 'admin'))
        ;
        yield TextareaField::new('statuses', 'Statuses')
            ->setHelp($this->translator->trans('List of case statuses (one per line). Board members will be able to see cases with the last status.',
                [], 'admin'))
        ;
    }

    public function delete(AdminContext $context)
    {
        try {
            parent::delete($context);
        } catch (EntityRemoveException $e) {
            // Display flash message
            if (str_contains($e->getMessage(), 'ForeignKeyConstraintViolationException')) {
                $this->addFlash('danger', new TranslatableMessage('Could not delete, as one or more other entities is related to this entity.', [], 'admin'));
            } else {
                $this->addFlash('danger', new TranslatableMessage('Something went wrong when attempting to delete complaint category.', [], 'admin'));
            }
        }

        return $this->redirect($this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->unset(EA::ENTITY_ID)->generateUrl());
    }
}

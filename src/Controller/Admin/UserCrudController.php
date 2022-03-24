<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_EDIT, Action::DELETE)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('edit', 'Edit user information')
            // Disable search bar
            ->setSearchFields(null)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield EmailField::new('email', 'Email')
            ->setFormTypeOptions(['disabled' => true])
        ;
        yield TextField::new('name', 'Name')
            ->setFormTypeOptions(['disabled' => true])
        ;
        yield TextField::new('initials', 'Initials');
        yield AssociationField::new('favoriteMunicipality', 'Favorite municipality')
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                return $queryBuilder->orderBy('entity.name', 'ASC');
            })
        ;
        yield TextareaField::new('shortcuts', 'Shortcuts')
            ->setHelp($this->translator->trans("List of shortcuts (one per line). Format: 'Identifier: URL', e.g. Aarhus Kommune: https://www.aarhus.dk/. Identifier must not contain a colon (:).", [], 'admin'))
        ;
    }

    /**
     * Throws exception if user attempts to visit index page.
     */
    public function index(AdminContext $context)
    {
        throw new AccessDeniedException('Access denied');
    }
}

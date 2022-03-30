<?php

namespace App\Controller\Admin;

use App\Entity\Municipality;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Translation\TranslatableMessage;

class MunicipalityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Municipality::class;
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
            ->setPageTitle('new', 'Add municipality')
            ->setEntityLabelInSingular('Municipality')
            ->setEntityLabelInPlural('Municipalities')
            ->setSearchFields(['name'])
            ->setDefaultSort(['name' => 'ASC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name');
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

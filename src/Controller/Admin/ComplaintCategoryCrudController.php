<?php

namespace App\Controller\Admin;

use App\Entity\Board;
use App\Entity\ComplaintCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Translation\TranslatableMessage;

class ComplaintCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ComplaintCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Add complaint category')
            ->setEntityLabelInSingular('Complaint category')
            ->setEntityLabelInPlural('Complaint categories')
            ->setSearchFields(['name', 'fee'])
            ->setDefaultSort(['name' => 'ASC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name');
        yield NumberField::new('fee', 'Fee')
            ->setRequired(true)
        ;
        yield TextField::new('kle', 'KLE')
            // Avoid showing 'Null' or 'Tom' if kle is not set.
            ->formatValue(fn($value) => $value ?? ' ')
        ;
        yield AssociationField::new('boards', 'Boards')
            ->setRequired(true)
            ->formatValue(function ($value, ComplaintCategory $category) {
                $boards = $category->getBoards()->map(fn(Board $board) => $board->__toString());

                return implode(', ', $boards->getValues());
            })
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

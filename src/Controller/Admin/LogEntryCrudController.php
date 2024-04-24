<?php

namespace App\Controller\Admin;

use App\Monolog\Admin\Field\JsonField;
use App\Monolog\LogEntry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LogEntryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LogEntry::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Log entries')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Log entry')
            ->setDefaultSort(['createdAt' => 'DESC'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('createdAt', 'Created at')
            // @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
        yield TextField::new('levelName');
        yield TextField::new('message');
        yield TextareaField::new('formatted')
            ->setCssClass('preformatted')
            ->onlyOnDetail()
        ;
        yield JsonField::new('context')
            ->setCssClass('preformatted')
            ->onlyOnDetail()
        ;
        yield JsonField::new('extra')
            ->setCssClass('preformatted')
            ->onlyOnDetail()
        ;
    }
}

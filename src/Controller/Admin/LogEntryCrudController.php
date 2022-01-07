<?php

namespace App\Controller\Admin;

use App\Monolog\LogEntry;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class LogEntryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LogEntry::class;
    }
}

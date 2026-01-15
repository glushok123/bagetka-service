<?php

namespace App\Controller\Admin;

use App\Entity\Employee;
use App\Enum\OfficeType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EmployeeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Employee::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setPageTitle('index', 'Сотрудники')
            ->setPageTitle('new', 'Добавить сотрудника')
            ->setPageTitle('edit', 'Редактировать сотрудника');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('fullName', 'ФИО'),
            ChoiceField::new('officeType', 'Мастерская')
                ->setFormTypeOption('choice_label', static function ($choice) {
                    return $choice->value;
                })
                ->setChoices(OfficeType::cases()),
        ];
    }
}

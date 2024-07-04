<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\User;
use App\Enum\OfficeType;
use App\Enum\RoleUser;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Заказы')
            ->setPageTitle('detail', fn (Order $order) => (string) $order->getNumber())
            ->setPageTitle('edit', fn (Order $order) => (string) $order->getNumber())
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('number', 'Номер'),
            TextField::new('phone', 'Телефон'),
            ChoiceField::new('officeType', 'Офис')
                ->setFormTypeOption('choice_label', function($choice) {
                    return $choice->value;
                })
                ->setChoices(OfficeType::cases())
                ->onlyOnForms()
            ,
            BooleanField::new('isImportant', 'Важный'),
            BooleanField::new('isCreateManager', 'Создан менеджером'),
            BooleanField::new('isFinished', 'Закрыт'),
            BooleanField::new('isSendSms', 'Отправлено СМС'),
            TextField::new('comment', 'Комментарий'),
            TextField::new('officeType.value', 'Офис')->onlyOnIndex(),
            DateTimeField::new('createdAt', 'Дата выполнения'),
            BooleanField::new('isDeleted', 'Удален')

        ];
    }
}

<?php

namespace App\Controller\Admin;

use App\Dto\User\UserDto;
use App\Entity\Embeddable\Hash;
use App\Entity\Materials;
use App\Entity\Order;
use App\Entity\User;
use App\Enum\NotificationEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        //return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(OrderCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Багетная мастерская');
    }

    public function configureMenuItems(): iterable
    {
        //yield MenuItem::linkToDashboard('', 'fa fa-home');
        yield MenuItem::linkToCrud('Пользователи', 'fas fa-user', User::class)->setPermission('ROLE_ADMIN');
        //yield MenuItem::linkToCrud('Заказы (список)', 'fas fa-list', Order::class);
        //yield MenuItem::linkToCrud('Материалы (список)', 'fas fa-list', Materials::class);
        //yield MenuItem::linkToCrud('Доставка (список)', 'fas fa-list', Delivery::class);

        yield MenuItem::linkToRoute('Заказы (Календарь)', 'fas fa-calendar', 'order_index');
        yield MenuItem::linkToRoute('Материалы (Календарь)', 'fas fa-calendar', 'materials_index');
    }
}

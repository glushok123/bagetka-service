<?php

namespace App\Controller;

use App\Controller\Admin\DashboardController;
use App\Service\MaterialsService;
use App\Service\OrderService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Factory\AdminContextFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class MaterialsController extends AbstractController
{
    public function __construct(
        private readonly MaterialsService $service
    ){

    }

    #[Route('/materials', name: 'materials_index')]
    public function index(
        Request             $request,
        AdminContextFactory $adminContextFactory,
        Environment         $twig
    ): Response
    {
        $dashboardController = new DashboardController();
        $dashboardController->setContainer($this->container);

        $adminContext = $adminContextFactory->create($request, $dashboardController, null);

        // required by @EasyAdmin/page/content.html.twig
        $twig->addGlobal('ea', $adminContext);

        // required by MenuFactory
        $request->attributes->set(EA::CONTEXT_REQUEST_ATTRIBUTE, $adminContext);

        return $this->render('materials/index.html.twig', [
            'controller_name' => 'MaterialsController',
        ]);
    }
}

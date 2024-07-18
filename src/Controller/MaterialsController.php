<?php

namespace App\Controller;

use App\Controller\Admin\DashboardController;
use App\Dto\Materials\MaterialsDto;
use App\Dto\Order\OrderDto;
use App\Dto\RequestGetCollectionDto;
use App\Entity\User;
use App\Service\MaterialsService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Factory\AdminContextFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse as Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Twig\Environment;

class MaterialsController extends AbstractController
{
    public function __construct(
        private readonly MaterialsService $service
    )
    {

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
        $twig->addGlobal('ea', $adminContext);
        $request->attributes->set(EA::CONTEXT_REQUEST_ATTRIBUTE, $adminContext);

        return $this->render('materials/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/materials/get-collection', name: 'materials_get_collection', methods: ['GET'])]
    public function getCollection(#[CurrentUser] ?User $user, #[MapQueryString] ?RequestGetCollectionDto $dto): Json
    {
        return $this->json(['result' => $this->service->getCollection($user, $dto ?? new RequestGetCollectionDto())]);
    }

    #[Route('/materials/get', name: 'materials_get', methods: ['GET'])]
    public function get(#[CurrentUser] ?User $user, #[MapQueryString] MaterialsDto $dto): Json
    {
        return $this->json(['result' => $this->service->get($user, $dto)]);
    }

    #[Route('/materials/create', name: 'materials_create', methods: ['POST'])]
    public function createOrder(#[CurrentUser] ?User $user, #[MapRequestPayload] MaterialsDto $dto): Json
    {
        return $this->json(['result' => $this->service->create($user, $dto)]);
    }

    #[Route('/materials/update', name: 'materials_update', methods: ['POST'])]
    public function updateOrder(#[CurrentUser] ?User $user, #[MapRequestPayload] MaterialsDto $dto): Json
    {
        return $this->json(['result' => $this->service->update($user, $dto)]);
    }
    #[Route('/materials/remove', name: 'materials_remove', methods: ['POST'])]
    public function removeOrder(#[CurrentUser] ?User $user, #[MapRequestPayload] MaterialsDto $dto): Json
    {
        return $this->json(['result' => $this->service->remove($user, $dto)]);
    }

}

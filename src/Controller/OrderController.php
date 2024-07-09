<?php

namespace App\Controller;

use App\Controller\Admin\DashboardController;
use App\Dto\Order\OrderDto;
use App\Dto\RequestGetCollectionDto;
use App\Dto\StatusDay\StatusDayDto;
use App\Entity\DaysOnWeek;
use App\Entity\User;
use App\Repository\DaysOnWeekRepository;
use App\Service\OrderService;
use DateInterval;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Factory\AdminContextFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Twig\Environment;
use Symfony\Component\HttpFoundation\JsonResponse as Json;

class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderService $service,
    )
    {

    }

    #[Route('/order', name: 'order_index')]
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

        $template = 'order/admin.html.twig';

        switch ($this->getUser()->getRole()->value) {
            case 'Менеджер':
                $template = 'order/manager.html.twig';
                break;
            case 'Админ':
                $template = 'order/admin.html.twig';
                break;
            case 'Мастер':
                $template = 'order/master.html.twig';
                break;
        }
        return $this->render($template, [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/', name: 'order_index_2')]
    public function index2(
        Request             $request,
        AdminContextFactory $adminContextFactory,
        Environment         $twig
    ): Response
    {

        if(empty($this->getUser())){
            return $this->redirect('/login');
        }

        $dashboardController = new DashboardController();
        $dashboardController->setContainer($this->container);
        $adminContext = $adminContextFactory->create($request, $dashboardController, null);
        $twig->addGlobal('ea', $adminContext);
        $request->attributes->set(EA::CONTEXT_REQUEST_ATTRIBUTE, $adminContext);

        $template = 'order/admin.html.twig';

        switch ($this->getUser()->getRole()->value) {
            case 'Менеджер':
                $template = 'order/manager.html.twig';
                break;
            case 'Админ':
                $template = 'order/admin.html.twig';
                break;
            case 'Мастер':
                $template = 'order/master.html.twig';
                break;
        }
        return $this->render($template, [
            'user' => $this->getUser(),
        ]);
    }
    #[Route('/order/get-collection', name: 'order_get_collection', methods: ['GET'])]
    public function getCollection(#[CurrentUser] ?User $user, #[MapQueryString] ?RequestGetCollectionDto $dto): Json
    {
        return $this->json(['result' => $this->service->getCollection($user, $dto ?? new RequestGetCollectionDto())]);
    }
    #[Route('/order/get', name: 'order_get', methods: ['GET'])]
    public function get(#[CurrentUser] ?User $user, #[MapQueryString] OrderDto $dto): Json
    {
        return $this->json(['result' => $this->service->get($user, $dto)]);
    }

    #[Route('/order/get-collection-week', name: 'order_get_collection_week', methods: ['GET'])]
    public function getCollectionWeek(#[CurrentUser] ?User $user, #[MapQueryString] ?RequestGetCollectionDto $dto): Json
    {
        return $this->json(['result' => $this->service->getCollectionWeek($user, $dto ?? new RequestGetCollectionDto())]);
    }

    #[Route('/order/create', name: 'order_create', methods: ['POST'])]
    public function createOrder(#[CurrentUser] ?User $user, #[MapRequestPayload] OrderDto $dto, Request $request): Json
    {
        return $this->json(['result' => $this->service->createOrder($user, $dto, $request->files)]);
    }

    #[Route('/order/update', name: 'order_update', methods: ['POST'])]
    public function updateOrder(#[CurrentUser] ?User $user, #[MapRequestPayload] OrderDto $dto, Request $request): Json
    {
        return $this->json(['result' => $this->service->updateOrder($user, $dto, $request->files)]);
    }

    #[Route('/order/remove', name: 'order_remove', methods: ['POST'])]
    public function removeOrder(#[CurrentUser] ?User $user, #[MapRequestPayload] OrderDto $dto): Json
    {
        return $this->json(['result' => $this->service->removeOrder($user, $dto)]);
    }

    #[Route('/order/update-status-day', name: 'order_update_status_day', methods: ['POST'])]
    public function updateStatusDay(#[CurrentUser] ?User $user, #[MapRequestPayload] StatusDayDto $dto): Json
    {
        return $this->json(['result' => $this->service->updateStatusDay($user, $dto)]);
    }
}
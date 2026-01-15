<?php

namespace App\Service;

use App\Dto\Order\OrderDto;
use App\Dto\Order\ReceiptDto;
use App\Dto\RequestGetCollectionDto;
use App\Dto\StatusDay\StatusDayDto;
use App\Entity\DaysOnWeek;
use App\Entity\Order;
use App\Entity\User;
use App\Enum\OfficeType;
use App\Repository\DaysOnWeekRepository;
use App\Repository\OrderRepository;
use DateInterval;
use DateTime;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;

class OrderService
{
    public function __construct(
        private readonly DaysOnWeekRepository $daysOnWeekRepository,
        private readonly OrderRepository      $orderRepository,
        private readonly FileService          $fileService,
    )
    {

    }


    public function getCollection(User $user, RequestGetCollectionDto $dto): array
    {
        $data = [];

        $week = $this->daysOnWeekRepository->findBy(['weekNumber' => $dto->weekNumber]);
        $weekEnd = $this->daysOnWeekRepository->findBy(['weekNumber' => $dto->weekNumber + 1]);
        $dateStart = $week[0]->getDateDay();
        $dateEnd = $weekEnd[6]->getDateDay();


        switch ($user->getRole()->value) {
            case 'Менеджер':
            case 'Админ':
                $orders = $this->orderRepository->getCollection($dateStart, $dateEnd, $dto->officeType);
                break;
            case 'Мастер':
                $orders = $this->orderRepository->findBy(['isDeleted' => false, 'officeType' => $user->getOfficeType()]);
                break;
        }

        foreach ($orders as $order) {
            $data[] = [
                'id' => $order->getId(),
                'number' => $order->getNumber(),
                'phone' => $order->getPhone(),
                'isSendSms' => $order->isSendSms(),
                'isFinished' => $order->isFinished(),
                'isCreateManager' => $order->isCreateManager(),
                'pdf' => $order->getPdf(),
                'jpeg' => $order->getJpeg(),
                'receiptPdf' => $order->getReceiptPdf(),
                'durationHours' => $order->getDurationHours(),
                'comment' => $order->getComment(),
                'officeType' => $order->getOfficeType()->value,
                'createdAt' => $order->getCreatedAt()->format('d.m.Y'),
                'isImportant' => $order->isImportant(),
                'isDeleted' => $order->isDeleted(),
                'isExpired' => $order->getCreatedAt() <= new \DateTime(),
            ];
        }

        return $data;
    }

    public function getCollectionWeek(User $user, RequestGetCollectionDto $dto): array
    {
        //$this->weeks_in_period('01.01.2024', '01.01.2035');
        $data = [];
        // $day = new DateTime();
        // $day = $day->format('Y-m-d');

        if ($currentDay = $this->daysOnWeekRepository->findOneBy(['dateDay' => new DateTime()])) {
            $week = $this->daysOnWeekRepository->findBy(['weekNumber' => $currentDay->getWeekNumber()]);

            if (!empty($dto->weekNumber)) {
                $week = $this->daysOnWeekRepository->findBy(['weekNumber' => $dto->weekNumber]);
            }
            $count = 1;
            foreach ($week as $day) {
                if ($count === 1) $dayName = 'mo';
                if ($count === 2) $dayName = 'tu';
                if ($count === 3) $dayName = 'we';
                if ($count === 4) $dayName = 'th';
                if ($count === 5) $dayName = 'fr';
                if ($count === 6) $dayName = 'sa';
                if ($count === 7) $dayName = 'su';

                $data[] = [
                    $dayName => [
                        'id' => $day->getId(),
                        'date' => $day->getDateDay()->format('d.m.Y'),
                        'statusArbat' => $day->isCloseArbat(),
                        'statusNov' => $day->isCloseNov(),
                        'statusBar' => $day->isCloseBarricad(),
                    ],
                ];
                $count = $count + 1;
            }
            $week = $this->daysOnWeekRepository->findBy(['weekNumber' => $currentDay->getWeekNumber() + 1]);

            if (!empty($dto->weekNumber)) {
                $week = $this->daysOnWeekRepository->findBy(['weekNumber' => $dto->weekNumber + 1]);
            }
            $count = 1;
            foreach ($week as $day) {
                if ($count === 1) $dayName = 'mo';
                if ($count === 2) $dayName = 'tu';
                if ($count === 3) $dayName = 'we';
                if ($count === 4) $dayName = 'th';
                if ($count === 5) $dayName = 'fr';
                if ($count === 6) $dayName = 'sa';
                if ($count === 7) $dayName = 'su';

                $data[] = [
                    $dayName => [
                        'id' => $day->getId(),
                        'date' => $day->getDateDay()->format('d.m.Y'),
                        'statusArbat' => $day->isCloseArbat(),
                        'statusNov' => $day->isCloseNov(),
                        'statusBar' => $day->isCloseBarricad(),
                    ],
                ];
                $count = $count + 1;
            }
        }

        return [
            'days' => $data,
            'weekNumber' => empty($dto->weekNumber) ? $currentDay->getWeekNumber() : $dto->weekNumber,
        ];
    }

    public function createOrder($user, OrderDto $dto, ?FileBag $files = null): array
    {
        $order = new Order();
        $order->setNumber($dto->number);
        $order->setPhone($dto->phone);
        $order->setIsDeleted(false);
        $order->setIsFinished($this->resolveFinishedState($order, $dto->isFinished));
        $order->setOfficeType(OfficeType::from($dto->officeType));
        $order->setCreatedAt($dto->date);
        $order->setIsImportant($dto->isImportant);
        $order->setDurationHours($this->normalizeDurationHours($dto->durationHours));

        if ($user->getRole()->value === 'Менеджер') {
            $order->setIsCreateManager(true);
        }

        if (!empty($files->get('pdf'))) {
            $filename = $this->fileService->save($files->get('pdf'));
            $order->setPdf($filename);
        }

        if ($files->get('jpeg')) {
            $filename = $this->fileService->save($files->get('jpeg'));
            $order->setJpeg($filename);
        }

        $order->setComment($dto->comment);

        $this->orderRepository->save($order);

        return ['success' => true];
    }

    public function get(User $user, OrderDto $dto): array
    {
        $order = $this->orderRepository->findOneBy(['id' => $dto->id]);
        $orderData = [
            'id' => $order->getId(),
            'number' => $order->getNumber(),
            'phone' => $order->getPhone(),
            'isSendSms' => $order->isSendSms(),
            'isFinished' => $order->isFinished(),
            'isCreateManager' => $order->isCreateManager(),
            'pdf' => $order->getPdf(),
            'jpeg' => $order->getJpeg(),
            'receiptPdf' => $order->getReceiptPdf(),
            'durationHours' => $order->getDurationHours(),
            'comment' => $order->getComment(),
            'officeType' => $order->getOfficeType()->value,
            'createdAt' => $order->getCreatedAt()->format('d.m.Y'),
            'isImportant' => $order->isImportant(),
            'isDeleted' => $order->isDeleted(),
        ];


        return $orderData;
    }

    public function updateOrder($user, OrderDto $dto, ?FileBag $files = null): array
    {
        $order = $this->orderRepository->findOneBy(['id' => $dto->orderId]);
        $order->setNumber($dto->number);
        $order->setPhone($dto->phone);
        $order->setIsDeleted(false);
        $order->setIsFinished($this->resolveFinishedState($order, $dto->isFinished));
        $order->setOfficeType(OfficeType::from($dto->officeType));
        $order->setCreatedAt($dto->date);
        $order->setIsImportant($dto->isImportant);
        $order->setDurationHours($this->normalizeDurationHours($dto->durationHours));


        if (!empty($files->get('pdf'))) {
            $filename = $this->fileService->save($files->get('pdf'));
            $order->setPdf($filename);
        }

        if ($files->get('jpeg')) {
            $filename = $this->fileService->save($files->get('jpeg'));
            $order->setJpeg($filename);
        }

        $order->setComment($dto->comment);

        $this->orderRepository->save($order);

        return ['success' => true];
    }

    public function createReceipt(ReceiptDto $dto): array
    {
        if ($dto->orderId === null) {
            return ['error' => 'Order not found'];
        }
        $order = $this->orderRepository->findOneBy(['id' => $dto->orderId]);
        if (!$order) {
            return ['error' => 'Order not found'];
        }

        $filename = $this->generateReceiptPdf($order, $dto);
        $order->setReceiptPdf($filename);
        $this->orderRepository->save($order);

        return ['receiptPdf' => $filename];
    }

    public function removeOrder($user, OrderDto $dto): array
    {
        $order = $this->orderRepository->findOneBy(['id' => $dto->orderId]);
        $this->orderRepository->remove($order);

        return ['success' => true];
    }

    public function updateStatusDay($user, StatusDayDto $dto): array
    {
        // dd($dto);
        $day = $this->daysOnWeekRepository->findOneBy(['dateDay' => $dto->day]);
        switch ($dto->officeType) {
            case 'Новокузнецкая':
                //dd($dto->typeDay ==)
                $day->setIsCloseNov($dto->typeDay === "false" ? false : true);
                break;
            case 'Арбатская':
                $day->setIsCloseArbat($dto->typeDay === "false" ? false : true);
                break;
            case 'Баррикадная':
                $day->setIsCloseBarricad($dto->typeDay === "false" ? false : true);
                break;
        }
        // dd($dto->typeDay, $day);
        $this->daysOnWeekRepository->save($day);

        return ['success' => true];
    }

    public function checkStatusDay($user, StatusDayDto $dto): array
    {
        $status = null;
        $day = $this->daysOnWeekRepository->findOneBy(['dateDay' => $dto->day]);
        switch ($dto->officeType) {
            case 'Новокузнецкая':
                //dd($dto->typeDay ==)
                $status = $day->isCloseNov();
                break;
            case 'Арбатская':
                $status = $day->isCloseArbat();
                break;
            case 'Баррикадная':
                $status = $day->isCloseBarricad();
                break;
        }

        if ($status === null) {
            $status = false;
        }

        return ['status' => $status];
    }

    private function normalizeDurationHours(?int $durationHours): int
    {
        $allowed = [2, 4, 6, 8, 10, 12];
        if ($durationHours === null) {
            return 2;
        }
        if (!in_array($durationHours, $allowed, true)) {
            return 2;
        }

        return $durationHours;
    }

    private function resolveFinishedState(Order $order, ?bool $isFinished): bool
    {
        if ($order->getReceiptPdf() === null) {
            return false;
        }

        return $isFinished ?? false;
    }

    private function generateReceiptPdf(Order $order, ReceiptDto $dto): string
    {
        $filename = sprintf('receipt_%s.pdf', md5(uniqid((string) $order->getId(), true)));
        $filepath = FileService::PATH_FILE . $filename;

        $lines = [
            'Receipt for order #' . $order->getNumber(),
            'Name: ' . ($dto->fullName ?? ''),
            'Amount: ' . ($dto->amount ?? ''),
        ];

        $content = $this->buildSimplePdf($lines);
        file_put_contents($filepath, $content);

        return $filename;
    }

    private function buildSimplePdf(array $lines): string
    {
        $escapedLines = array_map([$this, 'escapePdfText'], $lines);
        $textLines = [];
        foreach ($escapedLines as $index => $line) {
            if ($index === 0) {
                $textLines[] = sprintf('50 750 Td (%s) Tj', $line);
            } else {
                $textLines[] = sprintf('0 -16 Td (%s) Tj', $line);
            }
        }

        $stream = "BT\n/F1 12 Tf\n" . implode("\n", $textLines) . "\nET";

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n",
            "4 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream\nendobj\n",
            "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPosition . "\n%%EOF";

        return $pdf;
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    public function weeks_in_period($dateStart, $dateEnd)
    {
        $dates = [];
        // $weeks = [];
        //$from = strtotime($dateStart);
        // $to = strtotime($dateEnd);

        $week = 1;
        $dateStart = new DateTime($dateStart);
        $dateEnd = new DateTime($dateEnd);
        // $days = (int)$date->format('t'); // total number of days in the month

        //dd($days);
        $oneDay = new DateInterval('P1D');


        while ($dateStart < $dateEnd) {
            $dates[$week] [] = $dateStart->format('Y-m-d');

            $dayOfWeek = $dateStart->format('l');
            if ($dayOfWeek === 'Sunday') {
                $week++;
            }

            $dateStart->add($oneDay);
        }

        foreach ($dates as $key => $value) {
            foreach ($value as $item) {
                $day = new DaysOnWeek();
                $day->setCloseDay(0);
                $day->setDateDay(new DateTime($item));
                $day->setWeekNumber($key);
                $this->daysOnWeekRepository->save($day);
            }
        }

        return $dates;
    }
}

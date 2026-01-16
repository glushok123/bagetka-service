<?php

namespace App\Service;

use App\Dto\Order\OrderDto;
use App\Dto\Order\ReceiptDto;
use App\Dto\RequestGetCollectionDto;
use App\Dto\StatusDay\StatusDayDto;
use App\Entity\DaysOnWeek;
use App\Entity\Employee;
use App\Entity\Order;
use App\Entity\User;
use App\Enum\OfficeType;
use App\Repository\DaysOnWeekRepository;
use App\Repository\EmployeeRepository;
use App\Repository\OrderRepository;
use DateInterval;
use DateTime;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class OrderService
{
    public function __construct(
        private readonly DaysOnWeekRepository $daysOnWeekRepository,
        private readonly OrderRepository      $orderRepository,
        private readonly EmployeeRepository   $employeeRepository,
        private readonly FileService          $fileService,
        private readonly Environment          $twig,
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
        $durationHours = $this->normalizeDurationHours($dto->durationHours);
        $officeType = OfficeType::from($dto->officeType);
        $validationError = $this->validateDailyCapacity($dto->date, $officeType, $durationHours);
        if ($validationError !== null) {
            return ['error' => $validationError];
        }

        $order = new Order();
        $order->setNumber($dto->number);
        $order->setPhone($dto->phone);
        $order->setIsDeleted(false);
        $order->setIsFinished($this->resolveFinishedState($order, $dto->isFinished));
        $order->setOfficeType($officeType);
        $order->setCreatedAt($dto->date);
        $order->setIsImportant($dto->isImportant);
        $order->setDurationHours($durationHours);

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
        if ($this->isReceiptLocked($order) && $order->isFinished() !== true) {
            $order->setIsFinished(true);
            $this->orderRepository->save($order);
        }
        $employees = $this->getEmployeesByOfficeType($order->getOfficeType());
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
            'receiptEmployeeId' => $order->getReceiptEmployee()?->getId(),
            'receiptEmployeeName' => $order->getReceiptEmployee()?->getFullName(),
            'receiptAmount' => $order->getReceiptAmount(),
            'receiptCreatedAt' => $order->getReceiptCreatedAt()?->format('c'),
            'durationHours' => $order->getDurationHours(),
            'comment' => $order->getComment(),
            'officeType' => $order->getOfficeType()->value,
            'createdAt' => $order->getCreatedAt()->format('d.m.Y'),
            'isImportant' => $order->isImportant(),
            'isDeleted' => $order->isDeleted(),
            'isLocked' => $this->isReceiptLocked($order),
            'employees' => $employees,
        ];


        return $orderData;
    }

    public function updateOrder($user, OrderDto $dto, ?FileBag $files = null): array
    {
        $order = $this->orderRepository->findOneBy(['id' => $dto->orderId]);
        if ($this->isReceiptLocked($order)) {
            return ['error' => 'Заказ закрыт для изменений'];
        }
        $durationHours = $this->normalizeDurationHours($dto->durationHours);
        $officeType = OfficeType::from($dto->officeType);
        $validationError = $this->validateDailyCapacity($dto->date, $officeType, $durationHours, $order->getId());
        if ($validationError !== null) {
            return ['error' => $validationError];
        }
        $order->setNumber($dto->number);
        $order->setPhone($dto->phone);
        $order->setIsDeleted(false);
        $order->setIsFinished($this->resolveFinishedState($order, $dto->isFinished));
        $order->setOfficeType($officeType);
        $order->setCreatedAt($dto->date);
        $order->setIsImportant($dto->isImportant);
        $order->setDurationHours($durationHours);


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
        if ($dto->employeeId === null || $dto->amount === null || $dto->amount === '') {
            return ['error' => 'Заполните сотрудника и сумму'];
        }
        if ($this->isReceiptLocked($order)) {
            return ['error' => 'Чек нельзя изменить'];
        }
        if (!is_numeric($dto->amount) || (float) $dto->amount <= 0) {
            return ['error' => 'Сумма должна быть больше нуля'];
        }
        $employee = $this->employeeRepository->findOneBy([
            'id' => $dto->employeeId,
            'officeType' => $order->getOfficeType(),
        ]);
        if (!$employee) {
            return ['error' => 'Сотрудник не найден'];
        }

        $filename = $this->generateReceiptPdf($order, $employee, $dto->amount);
        $order->setReceiptPdf($filename);
        $order->setReceiptEmployee($employee);
        $order->setReceiptAmount($dto->amount);
        $order->setReceiptCreatedAt(new \DateTimeImmutable());
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

    private function validateDailyCapacity(?\DateTimeImmutable $date, OfficeType $officeType, int $durationHours, ?int $excludeOrderId = null): ?string
    {
        if ($date === null) {
            return null;
        }

        $totalHours = $this->orderRepository->getTotalDurationHoursByDay($date, $officeType, $excludeOrderId);
        if (($totalHours + $durationHours) > 12) {
            return 'Нельзя занять больше 12 часов на день';
        }

        return null;
    }

    private function getEmployeesByOfficeType(OfficeType $officeType): array
    {
        return array_map(
            static fn (Employee $employee) => [
                'id' => $employee->getId(),
                'fullName' => $employee->getFullName(),
            ],
            $this->employeeRepository->findBy(['officeType' => $officeType], ['fullName' => 'ASC'])
        );
    }

    private function isReceiptLocked(Order $order): bool
    {
        $receiptCreatedAt = $order->getReceiptCreatedAt();
        if ($order->getReceiptPdf() === null || $receiptCreatedAt === null) {
            return false;
        }

        return $receiptCreatedAt <= (new \DateTimeImmutable('-1 hour'));
    }

    private function resolveFinishedState(Order $order, ?bool $isFinished): bool
    {
        if ($order->getReceiptPdf() === null) {
            return false;
        }

        return $isFinished ?? false;
    }

    private function generateReceiptPdf(Order $order, Employee $employee, string $amount): string
    {
        $filename = sprintf('receipt_%s.pdf', md5(uniqid((string) $order->getId(), true)));
        $filepath = FileService::PATH_FILE . $filename;

        (new Filesystem())->mkdir(\dirname($filepath));

        $total = number_format((float)$amount, 2, '.', '');

        $meta = $this->getReceiptMetaByOffice($order->getOfficeType());
        [$addr1, $addr2] = $this->splitAddressToTwoLines($meta['address']);

        $vars = [
            'inn'   => $meta['inn'],
            'sno'   => $meta['sno'],
            'shift' => '1',

            'kt'         => 'КТ0001',
            'item_name'  => 'ОФОРМЛЕНИЕ В БАГЕТ',
            'qty'        => '1.000',
            'item_sum'   => $total,
            'department' => '01',

            'total'   => $total,
            'cash'    => $total,
            'внесено' => $total,
            'сдача'   => '0.00',

            'date'     => (new \DateTimeImmutable())->format('d.m.Y'),
            'master'   => mb_strtoupper($employee->getFullName(), 'UTF-8'),
            'check_no' => (string)$order->getId(),
            'order_no' => (string)$order->getNumber(),

            'ip_name'       => mb_strtoupper($meta['ip_name'], 'UTF-8'),
            'address' => mb_strtoupper($meta['address'], 'UTF-8'),
            'address_line1' => mb_strtoupper($addr1, 'UTF-8'),
            'address_line2' => mb_strtoupper($addr2, 'UTF-8'),
            'place'         => mb_strtoupper($meta['place'], 'UTF-8'),
        ];

        $html = $this->twig->render('receipt/receipt.html.twig', $vars);

        // 58mm ширина как у чека, высоту можно поставить с запасом
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [58, 170],       // мм (если будет длиннее — увеличим)
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'default_font' => 'dejavusans',
            'tempDir' => sys_get_temp_dir(), // важно на хостингах с правами
        ]);

        $mpdf->WriteHTML($html);
        $mpdf->Output($filepath, Destination::FILE);

        return $filename;
    }

    private function splitAddressToTwoLines(string $address): array
    {
        $address = trim(preg_replace('/\s+/u', ' ', $address) ?? $address);

        if (preg_match('/^(.*?,)\s*(Д\..*)$/ui', $address, $m)) {
            return [trim($m[1]), trim($m[2])];
        }

        return [$address, ''];
    }

    private function getReceiptMetaByOffice(\App\Enum\OfficeType $officeType): array
    {
        // заполни под свои точки; здесь пример под Баррикадную
        return match ($officeType) {
            OfficeType::barricade => [
                'inn'     => '500601498899',
                'sno'     => 'ПАТЕНТ',
                'ip_name' => 'ИП ВАСЮКОВ ИГОРЬ ГЕННАДЬЕВИЧ',
                'address' => '123242,  Г. МОСКВА, УЛ. БАРРИКАДНАЯ, Д. 21/34 СТР. 3',
                'place'   => 'БАГЕТНАЯ МАСТЕРСКАЯ №1',
            ],
            OfficeType::arbatskaya => [
                'inn'     => '500601498899',
                'sno'     => 'ПАТЕНТ',
                'ip_name' => 'ИП ВАСЮКОВ ИГОРЬ ГЕННАДЬЕВИЧ',
                'address' => '119019, г. москва, ул. Арбат, д.1',
                'place'   => 'БАГЕТНАЯ МАСТЕРСКАЯ №1',
            ],
            OfficeType::novokuznetsk => [
                'inn'     => '500601498899',
                'sno'     => 'ПАТЕНТ',
                'ip_name' => 'ИП ВАСЮКОВ ИГОРЬ ГЕННАДЬЕВИЧ',
                'address' => '115184, г. москва, пер. Климентовский, д. 6',
                'place'   => 'БАГЕТНАЯ МАСТЕРСКАЯ №1',
            ],
            default => [
                'inn'     => '500601498899',
                'sno'     => 'ПАТЕНТ',
                'ip_name' => 'ИП ВАСЮКОВ ИГОРЬ ГЕННАДЬЕВИЧ',
                'address' => '123242,  Г. МОСКВА, УЛ. БАРРИКАДНАЯ, Д. 21/34 СТР. 3',
                'place'   => 'БАГЕТНАЯ МАСТЕРСКАЯ №1',
            ],
        };
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

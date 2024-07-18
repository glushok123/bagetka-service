<?php

namespace App\Service;

use App\Dto\Materials\MaterialsDto;
use App\Dto\RequestGetCollectionDto;
use App\Entity\Materials;
use App\Entity\User;
use App\Enum\OfficeType;
use App\Repository\MaterialsRepository;

class MaterialsService
{
    public function __construct(
        private readonly MaterialsRepository $repository
    )
    {

    }

    public function getCollection(User $user, RequestGetCollectionDto $dto)
    {
        $data = [];

        if (!empty($dto->officeType)) {
            $materials = $this->repository->findBy(['officeType' => OfficeType::from($dto->officeType), 'isFinished' => false, 'isWork' => false], ['isImportant' => 'DESC']);
        } else {
            $materials = $this->repository->findBy(['isWork' => true], ['isFinished' => 'ASC'], 100);
        }

        foreach ($materials as $material) {
            $data[] = [
                'id' => $material->getId(),
                'text' => $material->getText(),
                'comment' => $material->getComment(),
                'isImportant' => $material->isImportant(),
                'officeType' => $material->getOfficeType()->value,
                'isFinished' => $material->isFinished(),
                'isWork' => $material->isWork(),
                'date' => $material->getCreatedAt()->format('d.m'),
            ];
        }

        return $data;
    }

    public function get(User $user, MaterialsDto $dto)
    {
        $material = $this->repository->findOneBy(['id' => $dto->id]);

        $data = [
            'id' => $material->getId(),
            'text' => $material->getText(),
            'comment' => $material->getComment(),
            'isImportant' => $material->isImportant(),
            'officeType' => $material->getOfficeType()->value,
            'isFinished' => $material->isFinished(),
            'isWork' => $material->isWork(),
            'date' => $material->getCreatedAt()->format('d.m.Y'),
        ];


        return $data;
    }

    public function create(User $user, MaterialsDto $dto)
    {
        $material = new Materials();
        $material->setIsWork(false);
        $material->setIsFinished(false);
        $material->setIsImportant($dto->isImportant);
        $material->setText($dto->text);
        $material->setOfficeType(OfficeType::from($dto->officeType));
        $material->setCreatedAt($dto->date);

        $this->repository->save($material);

        return ['success' => true];
    }

    public function update(User $user, MaterialsDto $dto)
    {
        $material = $this->repository->findOneBy(['id' => $dto->materialId]);
        //dd($material, $dto->isWork ?? $material->isWork());
        $material->setIsWork($dto->isWork ?? $material->isWork());
        $material->setIsFinished($dto->isFinished ?? $material->isFinished());
        $material->setIsImportant($dto->isImportant ?? $material->isImportant());
        $material->setText($dto->text ?? $material->getText());
        $material->setComment($dto->comment ?? $material->getComment());
        $material->setOfficeType(OfficeType::from($dto->officeType));
        $material->setCreatedAt($dto->date ?? $material->getCreatedAt());

        $this->repository->save($material);

        return ['success' => true];
    }

    public function remove($user, MaterialsDto $dto): array
    {
        $order = $this->repository->findOneBy(['id' => $dto->materialId]);
        $this->repository->remove($order);

        return ['success' => true];
    }
}
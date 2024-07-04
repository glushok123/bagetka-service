<?php

namespace App\Dto;

use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Loader\LoaderInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class BasicDto implements DtoInterface
{

   /* public function toArray(array $groups = []): array
    {
        $metadataFactory = new ClassMetadataFactory(new LoaderInterface()));
        $normalizer = new PropertyNormalizer($metadataFactory, null);
        $serializer = new Serializer([$normalizer, new DateTimeNormalizer(), new ObjectNormalizer()]);

        return $serializer->normalize(
            $this,
            null,
            ['groups' => $groups]
        );
    }*/
}

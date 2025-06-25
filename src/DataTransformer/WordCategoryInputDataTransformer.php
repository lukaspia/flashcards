<?php

declare(strict_types=1);


namespace App\DataTransformer;

use App\Entity\WordCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class WordCategoryInputDataTransformer implements DenormalizerInterface, SerializerAwareInterface
{
    /**
     * @var \App\DataTransformer\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $categoryId = null;

        if (is_array($data) && isset($data['id'])) {
            $categoryId = $data['id'];
        } elseif (is_int($data)) {
            $categoryId = $data;
        } else {
            return $this->serializer->denormalize($data, $type, $format, $context);
        }

        $wordCategory = $this->entityManager->getRepository(WordCategory::class)->find($categoryId);

        if (!$wordCategory) {
            throw new \InvalidArgumentException(sprintf('WordCategory with ID "%s" not found.', $categoryId));
        }

        return $wordCategory;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): bool {
        return $type === WordCategory::class && ((is_array($data) && isset($data['id'])) || is_int($data));
    }

    /**
     * @inheritDoc
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            WordCategory::class => true,
        ];
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }
}
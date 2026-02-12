<?php

declare(strict_types=1);

namespace App\Service\Word;

use App\Repository\WordCategoryRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class WordCategoryService implements WordCategoryServiceInterface
{
    public function __construct(
        private WordCategoryRepository $wordCategoryRepository,
        private CacheInterface $cache,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * @return array<int, array{id: int, name: string}>
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getAllCategories(): array
    {
        return $this->cache->get('word_categories_all', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            $categories = $this->wordCategoryRepository->findAllOrderedByName();

            return $this->serializer->normalize($categories, null, ['groups' => 'category:read']);
        });
    }
}

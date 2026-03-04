<?php

declare(strict_types=1);

namespace App\Service\Word;

use App\Entity\WordCategory;
use App\Repository\WordCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

readonly class WordCategoryService implements WordCategoryServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WordCategoryRepository $wordCategoryRepository,
        #[Target('word_category_cache')]
        private TagAwareCacheInterface $cache,
        private NormalizerInterface $normalizer
    ) {
    }

    /**
     * @return array<int, array{id: int, name: string}>
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getAllCategories(): array
    {
        return $this->cache->get('word_categories_all', function (ItemInterface $item): array {
            $item->tag(['word_categories']);
            $item->expiresAfter(3600);

            $categories = $this->wordCategoryRepository->findAllOrderedByName();

            return (array) $this->normalizer->normalize($categories, null, ['groups' => 'category:read']);
        });
    }

    /**
     * @param string $name
     * @return \App\Entity\WordCategory
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function createCategory(string $name): WordCategory
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Category name cannot be empty.');
        }

        $category = new WordCategory();
        $category->setName($name);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $this->cache->invalidateTags(['word_categories']);

        return $category;
    }

    /**
     * @param string $name
     * @return void
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function deleteCategory(string $name): void
    {
        $category = $this->wordCategoryRepository->findOneBy(['name' => $name]);

        if (!$category) {
            throw new \InvalidArgumentException(sprintf('Category "%s" not found.', $name));
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        $this->cache->invalidateTags(['word_categories']);
    }
}
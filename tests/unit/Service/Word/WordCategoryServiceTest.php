<?php

namespace App\Tests\Service\Word;

use App\Entity\WordCategory;
use App\Repository\WordCategoryRepository;
use App\Service\Word\WordCategoryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class WordCategoryServiceTest extends TestCase
{
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|WordCategoryRepository $repository;
    private MockObject|TagAwareCacheInterface $cache;
    private MockObject|NormalizerInterface $normalizer;
    private WordCategoryService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(WordCategoryRepository::class);
        $this->cache = $this->createMock(TagAwareCacheInterface::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);

        $this->service = new WordCategoryService(
            $this->entityManager,
            $this->repository,
            $this->cache,
            $this->normalizer
        );
    }

    public function testGetAllCategoriesUsesCacheAndNormalizer(): void
    {
        $categories = [new WordCategory()];
        $normalizedData = [['id' => 1, 'name' => 'Test']];

        $this->cache->method('get')
            ->willReturnCallback(function (string $key, callable $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        $this->repository->method('findAllOrderedByName')->willReturn($categories);

        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($categories, null, ['groups' => 'category:read'])
            ->willReturn($normalizedData);

        $result = $this->service->getAllCategories();

        $this->assertEquals($normalizedData, $result);
    }

    public function testCreateCategorySuccess(): void
    {
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');
        $this->cache->expects($this->once())->method('invalidateTags')->with(['word_categories']);

        $category = $this->service->createCategory('Nowa');
        $this->assertEquals('Nowa', $category->getName());
    }

    public function testDeleteCategorySuccess(): void
    {
        $category = new WordCategory();
        $this->repository->method('findOneBy')->willReturn($category);

        $this->entityManager->expects($this->once())->method('remove')->with($category);
        $this->entityManager->expects($this->once())->method('flush');
        $this->cache->expects($this->once())->method('invalidateTags')->with(['word_categories']);

        $this->service->deleteCategory('Do usunięcia');
    }

    public function testCreateCategoryThrowsExceptionOnEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->createCategory('   ');
    }
}
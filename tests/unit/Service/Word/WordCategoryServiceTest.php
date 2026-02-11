<?php

declare(strict_types=1);

namespace App\Tests\Service\Word;

use App\Entity\WordCategory;
use App\Repository\WordCategoryRepository;
use App\Service\Word\WordCategoryService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class WordCategoryServiceTest extends TestCase
{
    public function testGetAllCategoriesReturnsMappedArrayAndUsesCache(): void
    {
        $category1 = $this->createWordCategory(1, 'Testowa1');
        $category2 = $this->createWordCategory(2, 'Testowa2');

        $repository = $this->createMock(WordCategoryRepository::class);
        $repository->expects($this->once())
            ->method('findAllOrderedByName')
            ->willReturn([$category1, $category2]);

        $item = $this->createMock(ItemInterface::class);
        $item->expects($this->once())
            ->method('expiresAfter')
            ->with(3600);

        $cache = $this->createMock(CacheInterface::class);

        $cacheStore = [];
        $callbackInvocations = 0;

        $cache->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) use (&$cacheStore, &$callbackInvocations, $item) {
                if (array_key_exists($key, $cacheStore)) {
                    return $cacheStore[$key];
                }

                $callbackInvocations++;
                $cacheStore[$key] = $callback($item);

                return $cacheStore[$key];
            });

        $service = new WordCategoryService($repository, $cache);

        $expected = [
            ['id' => 1, 'name' => 'Testowa1'],
            ['id' => 2, 'name' => 'Testowa2'],
        ];

        $this->assertSame($expected, $service->getAllCategories());
        $this->assertSame($expected, $service->getAllCategories());
        $this->assertSame(1, $callbackInvocations);
    }

    private function createWordCategory(int $id, string $name): WordCategory
    {
        $category = new WordCategory();
        $category->setName($name);

        $reflection = new \ReflectionClass($category);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($category, $id);

        return $category;
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Lesson;

use App\Entity\Word;
use Doctrine\Common\Collections\Collection;

interface WordImageServiceInterface
{
    /**
     * @param \App\Entity\Word $word
     * @return string|null
     */
    public function getWordImageFilePath(Word $word): ?string;

    /**
     * @param \App\Entity\Word $word
     * @return bool
     */
    public function removeWordImage(Word $word): bool;

    /**
     * @param \App\Entity\Word $word
     * @return bool
     */
    public function removeWordImageFile(Word $word): bool;

    /**
     * @param \Doctrine\Common\Collections\Collection $words
     * @return void
     */
    public function moveWordsImagesFromTemporary(Collection $words): void;
}
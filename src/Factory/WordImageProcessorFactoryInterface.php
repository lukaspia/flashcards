<?php

namespace App\Factory;

use App\Entity\Word;
use App\ImageProcessing\Word\WordImageProcessorInterface;

interface WordImageProcessorFactoryInterface
{
    /**
     * @param \App\Entity\Word|null $word
     * @return \App\ImageProcessing\Word\WordImageProcessorInterface
     */
    public function createProcessor(?Word $word): WordImageProcessorInterface;
}
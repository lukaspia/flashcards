<?php

namespace App\Factory;

use App\ImageProcessing\Word\WordImageProcessorInterface;

interface WordImageProcessorFactoryInterface
{
    /**
     * @param int $wordId
     * @return \App\ImageProcessing\Word\WordImageProcessorInterface
     */
    public function createProcessor(int $wordId): WordImageProcessorInterface;
}
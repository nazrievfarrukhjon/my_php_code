<?php

namespace App\Repositories;

readonly class FinderRepository
{
    public function __construct(private string $word)
    {
    }

    public function createLiveSearchIndex(): void
    {
        $singleSpaceSeparatedWord = str_replace('  ', ' ', $this->word);
        $words = explode(' ', $singleSpaceSeparatedWord);
        //todo do permutation
        //store in db
    }

}
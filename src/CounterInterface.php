<?php

declare(strict_types=1);

namespace App;

interface CounterInterface
{
    /**
     * Commits visit from given country.
     *
     * @param string $countryCode
     * @return void
     */
    public function commitVisit(string $countryCode): void;

    /**
     * Returns statistics for all countries.
     *
     * @return array
     */
    public function getAllCounts(): array;
}

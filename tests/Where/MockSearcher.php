<?php

namespace Tests\Where;

use SlothDevGuy\Searches\Searcher;

/**
 * Trait MockSearcher
 * @package Tests\Where
 */
trait MockSearcher
{
    /**
     * @return Searcher
     */
    protected function mockSearcher() : Searcher
    {
        $searcher = $this->createMock(Searcher::class);

        $searcher->expects($this->atLeastOnce())
            ->method('getFromQualifiedField')
            ->will($this->returnCallback(fn($field) => "test.{$field}"));

        return $searcher;
    }
}

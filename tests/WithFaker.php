<?php

namespace Tests;

use Faker\Factory;
use Faker\Generator;

/**
 * Trait WithFaker
 * @package Tests
 */
trait WithFaker
{
    /**
     * @var Generator
     */
    private $faker;

    /**
     * @return Generator
     */
    public function faker()
    {
        if(!$this->faker){
            $this->faker = Factory::create();
        }

        return $this->faker;
    }
}

<?php

namespace SlothDevGuy\Searches;

/**
 * Trait BuilderOptions
 * @package SlothDevGuy\Searches
 */
trait BuilderOptions
{
    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @param string $key
     * @param $default
     * @return array|mixed
     */
    public function option(string $key, $default = null)
    {
        return data_get($this->options, $key,  $default);
    }

    /**
     * @return array
     */
    public function options() : array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return array
     */
    public static function defaultOptions(array $options = []): array
    {
        return array_merge([], $options);
    }
}

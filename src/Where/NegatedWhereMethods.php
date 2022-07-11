<?php

namespace SlothDevGuy\Searches\Where;

/**
 * Trait NegatedWhereMethods
 * @package SlothDevGuy\Searches\Where
 */
trait NegatedWhereMethods
{
    /**
     * @return static
     */
    protected function negateIfRequired()
    {
        $arguments = $this->arguments();

        if($arguments->not()) {
            $arguments->method(static::$negatedMethods[$arguments->method()]);
        }

        return $this;
    }
}

<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use SlothDevGuy\Searches\Search;
use SlothDevGuy\Searches\Searcher;

if(!function_exists('morph_model_alias')){
    /**
     * Retrieve the register model alias in morph relationships
     *
     * @param Model|string $model
     * @param bool $strict
     * @return string|null
     */
    function morph_model_alias(Model|string $model, bool $strict = true) : string|null
    {
        $map = Relation::morphMap();
        $model = is_object($model)? get_class($model) : $model;

        $index = array_search($model, $map, $strict);

        if($index !== false)
            return $index;

        if(config('searches.morphs.build_default_aliases')){
            return Str::kebab(class_basename($model));
        }

        return null;
    }
}

if(!function_exists('eloquent_search')){
    /**
     * @param Model|string $from
     * @param mixed $conditions
     * @param array $options
     * @return Searcher
     */
    function eloquent_search(Model|string $from, mixed $conditions, array $options = []) : Searcher
    {
        return new Search($from, $conditions, null, $options);
    }
}

if (! function_exists('warning')) {
    /**
     * Write some information to the log.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    function warning($message, $context = [])
    {
        return app('Psr\Log\LoggerInterface')->warning($message, $context);
    }
}


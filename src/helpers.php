<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

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
            return $map[$index];

        if(config('searches.morphs.build_default_aliases')){
            return Str::kebab(class_basename($model));
        }

        return null;
    }
}

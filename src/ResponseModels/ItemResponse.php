<?php

namespace SlothDevGuy\Searches\ResponseModels;

use Closure;
use Illuminate\Database\Eloquent\Model;
use SlothDevGuy\Searches\Interfaces\ItemResponseInterface;

/**
 * Class ItemResponse
 * @package SlothDevGuy\Searches\ResponseModels
 */
class ItemResponse implements ItemResponseInterface
{
    /**
     * @var array
     */
    protected array $map = [];

    public function __construct(
        protected ?Model $item = null
    )
    {

    }

    /**
     * @return Model
     */
    public function item()
    {
        return $this->item;
    }

    public function map(): array
    {
        if(empty($this->map)){
            $this->map = $this->mapItem();
        }

        return $this->map;
    }

    protected function mapItem() : array
    {
        return $this->item()->toArray();
    }

    public function toArray()
    {
        return $this->map();
    }

    /**
     * @param int $options
     * @return false|string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public static function mapEach(): Closure
    {
        return function (Model $model){
            return new static($model);
        };
    }
}

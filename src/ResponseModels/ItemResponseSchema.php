<?php

namespace SlothDevGuy\Searches\ResponseModels;

use Closure;
use Illuminate\Database\Eloquent\Model;
use SlothDevGuy\Searches\Interfaces\ItemResponseSchemaInterface;

/**
 * Class ItemResponse
 * @package SlothDevGuy\Searches\ResponseModels
 */
class ItemResponseSchema implements ItemResponseSchemaInterface
{
    /**
     * @var array
     */
    protected array $map = [];

    /**
     * @var array|null
     */
    protected ?array $only = null;

    /**
     * @var array|null
     */
    protected ?array $except = null;

    /**
     * @param Model|null $item
     */
    public function __construct(
        protected ?Model $item = null
    )
    {

    }

    /**
     * @param mixed $item
     * @return Model
     */
    public function item($item = null)
    {
        if(!is_null($item)){
            $this->item = $item;
            $this->map = [];
        }

        return $this->item;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function map(): array
    {
        if(!$this->item()){
            return [];
        }

        if(empty($this->map)){
            $this->map = $this->mapItem();
        }

        $map = $this->map;

        if(!is_null($this->only)){
            $map = collect($map)
                ->only($this->only)
                ->toArray();
        }

        if(!is_null($this->except)){
            $map = collect($map)
                ->except($this->except)
                ->toArray();
        }

        return $map;
    }

    /**
     * @inheritDoc
     * @param array|null $keys
     * @return ItemResponseSchemaInterface
     */
    public function only(array $keys = null): ItemResponseSchemaInterface
    {
        $this->only = $keys;

        return $this;
    }

    /**
     * @inheritDoc
     * @param array|null $keys
     * @return ItemResponseSchemaInterface
     */
    public function except(array $keys = null): ItemResponseSchemaInterface
    {
        $this->except = $keys;

        return $this;
    }

    /**
     * @return array
     */
    protected function mapItem() : array
    {
        return $this->item()->toArray();
    }

    /**
     * @return array
     */
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

    /**
     * @return Closure
     */
    public static function mapEach(): Closure
    {
        return function (Model $model){
            return new static($model);
        };
    }
}

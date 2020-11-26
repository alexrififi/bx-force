<?php

namespace Medvinator\BxForce\Database;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;

class Builder
{
    /**
     * The model being queried.
     *
     * @var Model
     */
    protected $model;

    /**
     * @var bool
     */
    protected $withUserFields = false;

    /**
     * Get the model instance being queried.
     *
     * @return Model|static
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set a model instance for the model being queried.
     *
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Create a new instance of the model being queried.
     *
     * @param array $attributes
     * @return Model|$this
     * @throws ArgumentException
     * @throws SystemException
     */
    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance( $attributes );
    }

    /**
     * Save a new model and return the instance.
     *
     * @param array $attributes
     * @return Model|$this
     * @throws ArgumentException
     * @throws SystemException
     */
    public function create(array $attributes = [])
    {
        return tap( $this->newModelInstance( $attributes ), function ($instance) {
            $instance->save();
        } );
    }

    /**
     * Find a model by its primary key.
     *
     * @param mixed $id
     * @param array $columns
     * @return mixed|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function find($id, $columns = [ '*' ])
    {
        return $this->findBy( $this->getModel()->getKeyName(), $id, $columns );
    }

    /**
     * Find a model by field
     *
     * @param string $column
     * @param mixed  $value
     * @param array  $columns
     * @return mixed|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function findBy(string $column, $value, $columns = [ '*' ])
    {
        $model = $this->getModel();

        $entityObject = $this->query()
            ->setSelect( $columns )
            ->where( $column, $value )
            ->setLimit( 1 )
            ->fetchObject();

        if ( $entityObject === null ) {
            return null;
        }

        $model->exists = true;
        return $model->setEntityObject( $entityObject, $this->withUserFields );
    }

    public function withUserFields(): Builder
    {
        $this->withUserFields = true;
        return $this;
    }

    /**
     * @return Query
     * @throws ArgumentException
     * @throws SystemException
     */
    protected function query(): Query
    {
        return $this->getModel()->bxTable()::query();
    }
}

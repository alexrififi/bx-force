<?php

namespace Medvinator\BxForce\Models;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Support\Str;

abstract class Model
{
    use HasAttributes;
    use HasTimestamps;

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    protected const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    protected const UPDATED_AT = 'updated_at';

    /**
     * @var DataManager
     */
    public static $bxTable;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected static $primaryKey = 'ID';

    /**
     * Indicates if the model exists.
     *
     * @var bool
     */
    public $exists = false;

    /**
     * @var EntityObject
     */
    protected $entityObject;

    public function __construct(array $attributes = [])
    {
        $this->entityObject = static::$bxTable::createObject();

        foreach ($attributes as $key => $value) {
            $this->setAttribute( $key, $value );
        }

        $this->setDateFormat( 'Y-m-d' );
    }

    /**
     * @param EntityObject $entityObject
     */
    public function setBxEntityObject(EntityObject $entityObject): void
    {
        $this->entityObject = $entityObject;

        if ( !$this->exists ) {
            return;
        }

        collect( $this->entityObject->entity->getScalarFields() )
            ->keys()
            ->each( function ($key) {
                $this->setAttribute(
                    Str::lower( $key ),
                    $this->entityObject->{'get' . Str::studly( Str::lower( $key ) )}()
                );
            } );
    }

    /**
     * @param array $attributes
     * @return Model
     * @throws ArgumentException
     * @throws SystemException
     */
    public static function create(array $attributes = []): Model
    {
        $attributes = collect( $attributes )
            ->intersectByKeys( static::$bxTable::getEntity()->getScalarFields() )
            ->toArray();

        return tap( static::newInstance( static::$bxTable::createObject() ), function (Model $instance) use ($attributes) {
            $instance->fill( $attributes )->save();
        } );
    }

    /**
     * @param mixed    $id
     * @param string[] $columns
     * @return Model|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function find($id, $columns = [ '*' ]): ?self
    {
        $entityObject = static::$bxTable::query()
            ->setSelect( $columns )
            ->where( static::$primaryKey, $id )
            ->setLimit( 1 )
            ->fetchObject();

        return static::newInstance( $entityObject, true );
    }

    /**
     * Create a new instance of the model being queried.
     *
     * @param EntityObject|null $entityObject
     * @param bool              $exists
     * @return Model|null
     */
    protected static function newInstance(?EntityObject $entityObject, $exists = false): ?self
    {
        if ( $entityObject === null ) {
            return null;
        }

        return tap( new static(), static function (Model $model) use ($entityObject, $exists) {
            $model->exists = $exists;
            $model->setBxEntityObject( $entityObject );
        } );
    }

    public function fill(array $attributes): Model
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute( $key, $value );
        }

        return $this;
    }

    /**
     * Save the model to the database.
     *
     * @return bool
     */
    public function save()
    {
        $this->mergeAttributesFromClassCasts();

        if ( $this->exists ) {

        } else {
            $saved = $this->performInsert();
        }
    }

    protected function performInsert(): bool
    {
        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
        if ( $this->usesTimestamps() ) {
            $this->updateTimestamps();
        }

        // If the model has an incrementing key, we can use the "insertGetId" method on
        // the query builder, which will give us back the final inserted ID for this
        // table from the database. Not all tables have to be incrementing though.
        $attributes = $this->getAttributes();

        if ( empty( $attributes ) ) {
            return true;
        }

        foreach ($this->entityObject->entity->getScalarFields() as $key => $field) {
            if ( !isset( $attributes[ $key ] ) ) {
                continue;
            }

            $value = $attributes[ $key ];

            if ( $field instanceof DatetimeField ) {
                $value = $value ? DateTime::createFromPhp( $value->toDateTime() ) : new DateTime;
            } elseif ( $field instanceof DateField ) {
                $value = $value ? Date::createFromPhp( $value->toDateTime() ) : new Date;
            }

            $this->entityObject->{'set' . Str::studly( Str::lower( $key ) )}( $value );
        }

        $result = $this->entityObject->save();

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        return $result->isSuccess();
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute( $key );
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute( $key, $value );
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return !is_null( $this->getAttribute( $offset ) );
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute( $offset );
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute( $offset, $value );
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset( $this->attributes[ $offset ], $this->relations[ $offset ] );
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists( $key );
    }

    /**
     * Unset an attribute on the model.
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset( $key );
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Determine if the given relation is loaded.
     *
     * @param string $key
     * @return bool
     */
    public function relationLoaded($key)
    {
        return false;
    }
}

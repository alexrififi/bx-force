<?php

namespace Medvinator\BxForce\Database;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Illuminate\Support\Str;

/**
 * @method static create(array $attributes = [])
 * @method static find($id, array $columns = [ '*' ])
 * @method static findBy(string $field, $value, array $columns = [ '*' ])
 * @method static Builder withUserFields()
 */
abstract class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * @var string
     */
    public $bxTable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public $userFields = [];

    /**
     * @var EntityObject
     */
    protected $entityObject;

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * Create a new instance of the given model.
     *
     * @param array $attributes
     * @param bool  $exists
     * @return Model
     * @throws ArgumentException
     * @throws SystemException
     */
    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static( (array) $attributes );

        $model->exists = $exists;

        $model->setEntityObject( $this->bxTable()::createObject() );

        $model->mergeCasts( $this->casts );

        return $model;
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return Builder
     */
    public function newQuery()
    {
        return (new Builder)->setModel( $this );
    }

    /**
     * @return DataManager|string
     */
    public function bxTable(): string
    {
        return $this->bxTable;
    }

    /**
     * @param EntityObject $entityObject
     * @param bool         $withUserFields
     * @return $this
     */
    public function setEntityObject(EntityObject $entityObject, $withUserFields = false): self
    {
        $this->entityObject = $entityObject;

        $dates = collect( $this->dates );
        foreach ($this->entityObject->entity->getScalarFields() as $key => $field) {
            if ( $field instanceof DateField ) {
                $dates->push( $key );
            }

            if ( $this->exists ) {
                $this->setAttribute(
                    Str::lower( $key ),
                    $this->entityObject->{'get' . Str::studly( Str::lower( $key ) )}()
                );
            }
        }
        $this->dates = $dates->unique()->toArray();

//        if ( $withUserFields ) {
//            $ufId = $this->bxTable()::getUfId();
//            dd( $ufId );
//
//            $manager = UserFieldHelper::getInstance()->getManager();
//            //$fields = $manager->GetUserFields( $this->{$this->getKeyName()} );
//            $fields = $manager->GetUserFields( 'IBLOCK_1_SECTION' );
//            dd( $fields );
//        }

        return $this;
    }

    /**
     * Save the model to the database.
     *
     * @param array $options
     * @return bool
     * @throws ArgumentException
     * @throws SystemException
     */
    public function save(array $options = [])
    {
        $this->mergeAttributesFromClassCasts();

        // First we need to create a fresh query instance and touch the creation and
        // update timestamp on the model which are maintained by us for developer
        // convenience. Then we will just continue saving the model instances.
        if ( $this->usesTimestamps() ) {
            $this->updateTimestamps();
        }

        $attributes = $this->getAttributes();

        if ( $this->exists ) {
            unset( $attributes[ $this->entityObject->entity->getPrimary() ] );
        }

        foreach ($this->entityObject->entity->getScalarFields() as $key => $field) {
            if ( !isset( $attributes[ $key ] ) ) {
                continue;
            }

            $value = $attributes[ $key ];

            if ( $field instanceof DatetimeField ) {
                $value = $value ? DateTime::createFromTimestamp( $value ) : new DateTime;
            } elseif ( $field instanceof DateField ) {
                $value = $value ? Date::createFromTimestamp( $value ) : new Date;
            }

            $value = $field->cast( $value );

            $this->entityObject->{'set' . Str::studly( Str::lower( $key ) )}( $value );
        }

        $result = $this->entityObject->save();

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        return $result->isSuccess();
    }
}

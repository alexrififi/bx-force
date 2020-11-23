<?php

namespace Medvinator\BxForce\Models;

use Bitrix\Main\UserTable;
use Illuminate\Support\Carbon;
use Medvinator\BxForce\Database\Model;

/**
 * Class User
 *
 * @property int         $id
 * @property string      $login
 * @property string      $name
 * @property string      $last_name
 * @property Carbon|null $personal_birthday
 */
class User extends Model
{
    public $bxTable = UserTable::class;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'personal_birthday',
    ];
}

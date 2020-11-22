<?php

namespace Medvinator\BxForce\Models;

use Bitrix\Main\UserTable;
use Illuminate\Support\Carbon;

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
    public static $bxTable = UserTable::class;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'personal_birthday' => 'date',
    ];
}

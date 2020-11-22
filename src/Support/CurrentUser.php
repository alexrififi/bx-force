<?php

namespace Medvinator\BxForce\Support;

use Bitrix\Main\Engine\CurrentUser as BitrixCurrentUser;
use Medvinator\BxForce\Models\User;

class CurrentUser
{
    /**
     * @var User
     */
    protected static $cache;

    public static function get(): User
    {
        if ( static::$cache ) {
            return static::$cache;
        }

        return User::find( (int) BitrixCurrentUser::get()->getId() );
    }
}

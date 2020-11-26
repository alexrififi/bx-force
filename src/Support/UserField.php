<?php

namespace Medvinator\BxForce\Support;

use Bitrix\Main\UserField\Internal\UserFieldHelper;

class UserField
{
    /**
     * @param string $object
     * @param string $field
     * @param int    $id
     * @return array|bool|mixed
     */
    public static function get(string $object, string $field, int $id)
    {
        return UserFieldHelper::getInstance()
            ->getManager()
            ->GetUserFieldValue( $object, $field, $id );
    }
}

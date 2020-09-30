<?php

namespace Medvinator\BxForce\Support;

use CUserTypeManager;

class UserTypeManager
{
    /**
     * @return CUserTypeManager
     */
    public static function instance(): CUserTypeManager
    {
        global $USER_FIELD_MANAGER;
        return $USER_FIELD_MANAGER;
    }


    /**
     * @param string $object
     * @param string $field
     * @param int    $id
     * @return array|bool|mixed
     */
    public static function get(string $object, string $field, int $id)
    {
        return self::instance()->GetUserFieldValue( $object, $field, $id );
    }
}

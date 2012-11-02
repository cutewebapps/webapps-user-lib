<?php

/**
 * static function container to be used in view helpers and controllers
 */
class User_Auth
{
    /**
     * @return bool
     */
    public static function isRegistered()
    {
         return Sys_Global::isRegistered('USER_OBJECT');
    }
    
    /**
     * @return string
     */
    public static function getLogin()
    {
         return Sys_Global::get('USER_LOGIN');
    }
    /**
     * @return string
     */
    public static function getUserObject()
    {
         return Sys_Global::get('USER_OBJECT');
    }

    /**
     * returns true if user has any of the given roles
     * @param string|array $role
     * @return boolean
     */
    public static function hasRole( $role )
    {
         /** @var $objAccount User_Account */
         $objAccount = Sys_Global::get('USER_OBJECT');
         if ( is_object( $objAccount ) ) {
            return $objAccount->hasRole( $role );
         }

         return false;
    }
}
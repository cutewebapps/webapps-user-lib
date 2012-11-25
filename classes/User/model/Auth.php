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
    
    
    /**
     * @var App_Session_Namespace
     */
    public $objSession = null;
    /**
     *
     * @param string $strKey
     * @param mixed $default
     * @return mixed
     */
    public function getSession( $strKey, $default = '' )
    {
        if ( !is_object( $this->objSession ) ) {
            $strSessionName = 'backend';
            $this->objSession = new App_Session_Namespace( $strSessionName );
        }
        if  ( $strKey != '' ) {
            return $this->objSession->$strKey;
        } else {
            return $default;
        }
    }

    /**
     * @param string $strKey
     * @param mixed $strValue
     * @return void
     */
    public function setSession( $strKey, $strValue ) {
        if ( !is_object( $this->objSession ) ) {
            $strSessionName = 'backend';
            $this->objSession = new App_Session_Namespace( $strSessionName );
        }
        if  ( $strKey != '' ) {
            $this->objSession->$strKey = $strValue;
        }
        return $this;
    }


    /**
     * @return User_Account
     */
    public function getUser()
    {
        return Sys_Global::get('USER_OBJECT');
    }
    
    /**
     * array for keeping detected access roles in cache
     * @var array
     */
    protected $_lstAccessCache = array();
    /**
     * 
     * @param string $strResource
     * @return boolean
     */
    public function hasAccessTo( $strResource )
    {
        if ( !isset($this->_lstAccessCache[ $strResource ]) ) {
        
            if ( $this->hasRole( 'Administrator') ) 
                $this->_lstAccessCache[ $strResource ] = true;
            else 
                $this->_lstAccessCache[ $strResource ] = $this->getUser()->canAccess( $strResource );
        }
        
        return $this->_lstAccessCache[ $strResource ];
    }
    /**
     * Throws exception if there is no access
     * @param string $strResource
     * @return void
     */
    public function requireAccessTo( $strResource )
    {
        if ( !$this->hasAccessTo( $strResource ) )
            throw new App_Exception_AccessDenied();
    }
    
    /**
     * Throws exception if there is no access
     * @param string $strRoleName
     * @return void
     */    
    public function requireRole( $strRoleName ) 
    {
        if ( !$this->hasRole( $strRoleName ) )
            throw new App_Exception_AccessDenied();
    }
    /**
     * @return boolean
     */
    public function isLoggedIn()
    {
        return is_object( $this->getUser() );
    }    
    
    /**
     * @return string
     */
    public function getDisplayName()
    {
        $objUser = $this->getUser();
        if ( is_object( $objUser )) {
            if ( trim( $objUser->ucac_first.' '.$objUser->ucac_last ) )
                return trim( $objUser->ucac_first.' '.$objUser->ucac_last );
            
            return $objUser->ucac_login;
        }
        return 'Guest';
    }    
    
}
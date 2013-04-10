<?php

class User_Account_Table extends DBx_Table
{
    /**
     * @var string
     */
    protected $_name = 'uc_account';
    /**
     * @var string
     */
    protected $_primary = 'ucac_id';
    /**
     * @return string
     */
    public function getLoginColumnName()
    {
        return 'ucac_login';
    }
    /**
     * @return string
     */
    public function getPasswordColumnName()
    {
        return 'ucac_password';
    }
    /**
     * @param string $strName
     * @return User_Account
     */
    public function findByLogin( $strName )
    {
        $select = $this->select()->where( 'ucac_login = ? ', $strName );
        return $this->fetchRow( $select );
    }
}

class User_Account_List extends DBx_Table_Rowset
{
}

class User_Account_Form_Edit extends App_Form_Edit
{
    public function  createElements()
    {
        $this->allowEditing( array( 'ucac_login', 'ucac_password', 'ucac_comment',
                'ucac_status', 'ucac_first', 'ucac_last', 'ucac_email', 'ucac_phone' ));
    }
}

class User_Account_Form_Filter extends App_Form_Filter
{
    public function  createElements()
    {
        $this->allowFiltering( array( 'ucac_login', 'ucac_comment',
            'ucac_status', 'ucac_first', 'ucac_last', 'ucac_email', 'ucac_phone',
            'role' ));
    }
}

class User_Account extends DBx_Table_Row
{
    const ACTIVE = 1;
    const INACTIVE = 2;

    protected $_objRoles = null;
    public static function getClassName() { return 'User_Account'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    /**
     * @return void
     */
    public function cleanCache()
    {
        $this->_objRoles = null;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->ucac_name;
    }
    /**
     * Saves the properties to the database.
     *
     * This performs an intelligent insert/update, and reloads the
     * properties with fresh data from the table on success.
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function save( $bRefresh = true )
    {
        if ( $this->ucac_password == '' && $this->getOldData( 'ucac_password' ) != '' )
        {
            $this->ucac_password = $this->getOldData( 'ucac_password' );
        }
            
        if ( $this->ucac_password ) 
        {
            $this->ucac_hash = md5($this->ucac_password);
        }
        return parent::save( $bRefresh );
    }
    /**
     * Overriden method delete, for deprecate full delete of object from db.
     * @return void
     */
    public function delete()
    {
        $objConfig = App_Application::getInstance()->getConfig()->user;
        
        // config-based detection of what to do on deleting user
        // dometime we need to prevent deletion
        if ( $objConfig->on_delete ) {
            call_user_func_array( $objConfig->on_delete, array( 'object' => $this ) );
        } else if ( $objConfig->never_delete ) {
            $this->ucac_status = User_Account::INACTIVE;
            $this->save();        
        } else {
            parent::delete();
        }
    } 
    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->ucac_login;
    }
    /**
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->ucac_hash;
    }
    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->ucac_password;
    }
    /**
     * @return datetime
     */
    public function getDateAdded()
    {
        return date('Y-m-d H:i:s', strtotime( $this->ucac_date_added ) );
    }
    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->ucac_status;
    }
    /**
     * @return boolean
     */
    public function isActive()
    {
        return ( $this->getStatus() == User_Account::ACTIVE );
    }
    /**
     * @return string
     */
    public function getStatusName()
    {
        switch( $this->getStatus() ) {
            case User_Account::ACTIVE:   return 'Enabled';
            case User_Account::INACTIVE: return 'Disabled';
            default: return '('.$this->getStatus().')';
        }

    }
    /**
    * @return User_Role_List
    */
    public function getRoles()
    {
        $tblRoles =  User_Role::Table();
        $tblUserRole = User_UserRole::Table();
        if ( $this->_objRoles  == null ) {

            $selectRoles = $tblRoles->select()
                ->setIntegrityCheck( false )
                ->from( $tblRoles )
                ->joinInner( $tblUserRole->getTableName(), 'ucur_role_id = ucr_id' )
                ->where( 'ucur_user_id = ?', $this->getId() );

            $this->_objRoles = $tblRoles->fetchAll( $selectRoles );
        }
        return $this->_objRoles ;
    }
    /**
     * returns true if user has any of given roles...
     * @param string $role
     * @return boolean
     */
    public function hasRole( $role )
    {
        /** @var $objRole User_Role */
        foreach ( $this->getRoles() as $objRole ) {
            
            if ( is_array( $role ) ) {
                foreach ( $role as $strRole ) {
                    if ( $strRole == $objRole->getName() )
                        return true;
                }
                return false;
            } else {
                $strRole = $role;
                if ( $strRole == $objRole->getName() )
                    return true;
            }
        }
        return false;
    }
    /**
     * @param string $strRole
     * @return void
     */
    public function addRole( $strRole )
    {
        $objRole = User_Role::Table()->findByName( $strRole );
        if ( !is_object( $objRole ) )
            throw new App_Exception( 'Invalid user role '.$strRole );

        $nRoleId = $objRole->getId();
        $objUserRole = User_UserRole::Table()->findRole( $this->getId(), $nRoleId );
        if ( ! is_object( $objUserRole )) {
            $objUserRole = User_UserRole::Table()->addRole( $this->getId(), $nRoleId );
            $this->cleanCache();
        }
    }
    /**
     * @param string $strRole
     * @return void
     */
    public function removeRole ( $strRole ) {

        $objRole = User_Role::Table()->findByName( $strRole );
        if ( !is_object( $objRole ) )
            throw new App_Exception( 'Invalid user role '.$strRole );
        
        $nRoleId = $objRole->getId();
        $objUserRole = User_UserRole::Table()->findRole( $this->getId(), $nRoleId );
        if ( is_object( $objUserRole )) {
            $objUserRole->delete();
            $this->cleanCache();
        }
    }
    protected $_arrAccessList = null;
    /**
     * Get IDs of resources
     * @return array of int resources
     */
    public function getAccessList()
    {
        if ( $this->_arrAccessList == null ) {
            $this->_arrAccessList = array();
            /* @var $objRole User_Role */
            foreach ( $this->getRoles() as $objRole ) {
                $lstAccessList = $objRole->getAccessList();
                /* @var $objAccessList User_AccessList */
                foreach ( $lstAccessList as $objAccessList ) {
                   $this->_arrAccessList[ $objAccessList->getResourceId() ] = $objAccessList->getResourceId();
                }
            }
        }
        return $this->_arrAccessList;
    }
    /**
     * Check whether user has an acccess to resource by ID
     * @return boolean
     */
    public function canAccess( $resource )
    {
        $cache = new Sys_Cache_Memory();
        $strCacheIndex = 'user-'.$this->getId().'can-access-'.$resource;
        if ( ($res = $cache->load( $strCacheIndex )) === false ) {
        
            if ( preg_match( '@\d+@', $resource ) ) {
                $res = $this->canAccessByResourceId( $resource );
            } else {
                $nResourceId = User_Resource::parseString( $resource ); 
                if ( ! $nResourceId ) 
                    throw new User_Acl_Exception( "Invalid resource string: ".$resource );

                $res = $this->canAccessByResourceId( $nResourceId );
            }
            $cache->save( $res, $strCacheIndex );
        }
        return $res;
    }
    /**
     * @param integer $nResourceId
     * @return boolean
     */
    public function canAccessByResourceId( $nResourceId )
    {
        if ( $nResourceId instanceof Sys_Config )
            $nResourceId = $nResourceId->id;
            
        $arrAccessList = $this->getAccessList();
        return isset( $arrAccessList[ $nResourceId ] );
    }
}
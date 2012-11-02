<?php

class User_Role_Table extends DBx_Table
{
    /**
     * @var string
     */
    protected $_name = 'uc_role';
    
    /**
     * @var string
     */
    protected $_primary = 'ucr_id';

    /**
     *
     * @param string $strRole
     * @return User_Role
     */
    public function findByName( $strRole )
    {
        $select = $this->select()->where( 'ucr_name = ?', $strRole );
        return $this->fetchRow( $select );
    }
}

class User_Role_List extends DBx_Table_Rowset
{
}

class User_Role_Form_Filter extends App_Form_Filter
{
}

class User_Role_Form_Edit extends App_Form_Edit
{
}

/**
 * User_Role
 */
class User_Role extends DBx_Table_Row 
{
    public static function getClassName() { return 'User_Role'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    /**
     * @return int
     */
    public function getRoleId()
    {
        return $this->ucr_id;        
    }
    /**
     * @return string
     */
    public function getName() 
    {
        return $this->ucr_name;
    }

    /**
     * @return datetime
     */
    public function getCreatedDate()
    {
        return $this->ucr_date_added;
    }


    /**
     * @return void
     */
    public function _insert()
    {
        if ( !isset( $this->ucr_date_added ) )
            $this->ucr_date_added = date('Y-m-d H:i:s');
        
    }

    /**
     * @return void
     */
    public function _delete()
    {
        //delete all user-roles with this role
        $selectRoles = User_UserRole::Table()->select()->where('ucur_role_id = ?', $this->getId() );
        foreach ( User_UserRole::Table()->fetchAll( $selectRoles ) as $objUserRole) {
            $objUserRole->delete();
        }
        //delete all access list for this resource
        $selectList = User_AccessList::Table()->select()->where('ucal_role_id = ? ', $this->getId() );
        foreach ( User_AccessList::Table()->fetchAll( $selectList ) as $objAccessList) {
            $objAccessList->delete();
        }        
    }
}
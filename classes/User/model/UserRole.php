<?php

class User_UserRole_Table extends DBx_Table
{
    /**
     * @var string
     */
    protected $_name = 'uc_user_role';

    /**
     * @var string
     */
    protected $_primary = 'ucur_id';

    /**
     * @param integer $nUserId
     * @param integer $nRoleId
     * @return User_UserRole */
    public function findRole( $nUserId, $nRoleId )
    {
        $select = $this->select()
                ->where( 'ucur_user_id = ?', $nUserId )
                ->where( 'ucur_role_id = ?', $nRoleId );
        return $this->fetchRow( $select );
    }
    /**
     * @param integer $nUserId
     * @param integer $nRoleId
     * @return void
     */
    public function addRole( $nUserId, $nRoleId )
    {
        $objUser = User_UserRole::Table()->createRow();
        $objUser->ucur_user_id = $nUserId;
        $objUser->ucur_role_id = $nRoleId;
        $objUser->save( false );
    }
}

class User_UserRole_List extends DBx_Table_Rowset
{
}

class User_UserRole_Form_Filter extends App_Form_Filter
{
}

class User_UserRole_Form_Edit extends App_Form_Edit
{
}

class User_UserRole extends DBx_Table_Row
{
    public static function getClassName() { return 'User_UserRole'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    /**
     * @return int
     */
    public function getUserId()
    {
            return $this->ucur_user_id;
    }
    /**
     * @return int
     */
    public function getRoleId()
    {
            return $this->ucur_role_id;
    }
    /**
     * @return datetime
     */
    public function getDateAdded()
    {
            return $this->ucur_date_added;
    }


    protected function _insert()
    {
        if ( !isset( $this->ucur_date_added ) )
            $this->ucur_date_added = date('Y-m-d H:i:s');
        parent::_insert();
    }
}
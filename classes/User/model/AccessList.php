<?php

class User_AccessList_Table extends DBx_Table
{
    /**
     * @var string
     */
    protected $_name = 'uc_access_list';
    /**
     * @var string
     */
    protected $_primary = 'ucal_id';
}

class User_AccessList_List  extends DBx_Table_Rowset
{
}

/**
 */
class User_AccessList extends DBx_Table_Row
{
    public static function getClassName() { return 'User_AccessList'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }

    public function _insert()
    {
        if ( !isset( $this->ucal_date_added ) )
            $this->ucal_date_added = date('Y-m-d H:i:s');
    }
    /**
     * @return int
     */
    public function getRoleId()
    {
        return $this->ucal_role_id;
    }
    /**
     * @return string
     */
    public function getResourceId()
    {
        return $this->ucal_resource_id;
    }
    /**
     * @return datetime
     */
    public function getDateAdded()
    {
        return $this->ucal_date_added;
    }

}
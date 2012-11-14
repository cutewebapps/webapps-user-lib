<?php

class User_Option_Table extends DBx_Table
{
    /**
     * @var string
     */
    protected $_name = 'uc_option';
    
    /**
     * @var string
     */
    protected $_primary = 'uco_id';

}

class User_Option_List extends DBx_Table_Rowset
{
}

class User_Option_Form_Filter extends App_Form_Filter
{
   public function createElements()
   {    
        $this->allowFiltering( array( 
            'uco_user_id',
            'uco_key'
        ) );    
   }
}

class User_Option_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        $this->allowEditing( array( 
            'uco_user_id',
            'uco_key',
            'uco_value'
        ) );
    }
}

/**
 * User_Option
 */
class User_Option extends DBx_Table_Row 
{
    public static function getClassName() { return 'User_Option'; }
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
        return $this->uco_user_id;        
    }
    /**
     * @return string
     */
    public function getKey() 
    {
        return $this->uco_key;
    }

    /**
     * @return datetime
     */
    public function getCreatedDate()
    {
        return $this->uco_value;
    }

}
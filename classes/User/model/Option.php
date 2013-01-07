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

    public function deleteforUserId( $nUserId )
    {
        $this->getAdapterWrite()->queryWrite( 'DELETE FROM `'.$this->getTableName().'` WHERE uco_user_id = '.$nUserId );
    }
    /**
     * @param int $nUserId
     * @return User_Option_List
     */
    public function findByUserId( $nUserId )
    {
        $cache = new Sys_Cache_Memory();
        $strIndex = 'user-options-'.$nUserId;
        $lstOptions = $cache->load( $strIndex );
        if ( $lstOptions === false ) {
            $select = $this->select()->where( 'uco_user_id = ?', $nUserId );
            $lstOptions = $this->fetchAll( $select );
            $cache->save( $lstOptions, $strIndex );
        }        
        return $lstOptions;
    }
    
    /**
     * setting user option routine
     * @param int $nUserId
     * @param string $strKey
     * @param string $strValue
     * @return void
     */
    public function setValue( $nUserId, $strKey, $strValue ) 
    {
        $select = $this->select()->where( 'uco_user_id = ?', $nUserId )->where( 'uco_key = ?', $strKey );
        $objValue = $this->fetchRow( $select );
        if ( !is_object( $objValue ) ) {
            $objValue = $this->createRow();
            $objValue->uco_user_id = $nUserId;
            $objValue->uco_key = $strKey;
        }
        $objValue->uco_value = $strValue;
        $objValue->save( false );
    }
    
    public function serValuesArray( $nUserId, $arrValues )
    {
        foreach( $arrValues as $strKey => $strValue ) {
            $objValue = $this->createRow();
            $objValue->uco_user_id = $nUserId;
            $objValue->uco_key = $strKey;
            $objValue->uco_value = $strValue;
            $objValue->save( false );
        }
    }
    
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
    /** @return User_Option_Table */
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
     * @return string
     */
    public function getValue()
    {
        return $this->uco_value;
    }

}
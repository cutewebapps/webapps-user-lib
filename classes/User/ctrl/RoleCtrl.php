<?php

class User_RoleCtrl extends App_DbTableCtrl
{
    protected function _filterField( $strFieldName, $strFieldValue )
    {
        switch ( $strFieldName ) {
        default:
            parent::_filterField($strFieldName, $strFieldValue);
        }
    }
    
    public function getlistAction()
    {
        if ( $this->_hasParam('user') ) {
            $this->view->user = $this->_getParam( 'user' );
        }
        if ( $this->_hasParam('editable') ) {
            $this->view->isEditable = $this->_getParam( 'editable' );
        }
        parent::getlistAction();
    }
    
    public function editAction()
    {
     
        $arrError = array();
        
        if ( $this->_isPost() ) {
            if ( $this->_hasParam( 'ucr_name' ) ) {
                
                $strNewRole = trim( $this->_getParam( 'ucr_name' ));
                if ( $strNewRole == '' ) {
                    array_push( $arrError, array( 'ucr_name' => 'Role name cannot be empty' )); 
                } else {
                    // check for unique
                    $select = User_Role::Table()->select()->where( 'ucr_name = ?', $strNewRole );
                    if ( $this->_getIntParam( 'ucr_id' ) != 0 ) {
                        $select->where( 'ucr_id <> ?', $this->_getIntParam( 'ucr_id' ) );
                    }
                    $objUser = User_Role::Table()->fetchRow( $select );
                    if ( is_object( $objUser ) ) {
                        array_push( $arrError, array( 'ucr_name' => 'Role with such name already exists' )); 
                    }
                }
            }
            
        }
        
        if ( count( $arrError ) > 0 ) {
            $this->view->arrError = $arrError;
            $this->view->lstErrors = $arrError;
            $this->view->object = User_Role::Table()->createRow();
            return;
        }
  
        //add role if it is provided
        parent::editAction();
    }
}
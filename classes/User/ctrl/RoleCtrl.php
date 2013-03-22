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
        if ( $this->_isPost() && ! $this->_getParam( 'ucr_id' ) ) {
            
            $arrErrors = array();
            
            // if we are adding a new record, prevent adding with the same login
            $strNewRole = $this->_getParam( 'ucr_name' );
            if ( $strNewRole != "" ) {
                $objUser = User_Role::Table()->findByName( $strNewRole );
                if ( is_object( $objUser ) ) {
                    $arrErrors[] = 'Role with such name already exists'; 
                }
            }
            
            if ( count( $arrErrors ) > 0 ) {
                $this->view->lstErrors = $arrErrors;
                $this->view->object = User_Role::Table()->createRow();
                return;
            }
        }
  
        //add role if it is provided
        parent::editAction();
    }
}
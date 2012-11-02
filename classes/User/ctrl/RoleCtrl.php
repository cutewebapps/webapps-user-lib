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
}
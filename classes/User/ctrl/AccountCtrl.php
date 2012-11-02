<?php

class User_AccountCtrl extends App_DbTableCtrl
{
    protected function  _filterField($strFieldName, $strFieldValue) {
        switch ( $strFieldName ) {
            case 'role':
                $this->_select->where( 'ucac_id IN 
                       (SELECT ucur_user_id FROM '.User_UserRole::TableName().'
                        JOIN '.User_Role::TableName().' ON ucr_id=ucur_role_id
                        WHERE ucr_name=?)', $strFieldValue );
                break;
            default:
                parent::_filterField($strFieldName, $strFieldValue);
        }
    }

    public function getlistAction()
    {
        parent::getlistAction();
        // die( $this->_select );
    }

    public function editAction()
    {
        //add role if it is provided
        parent::editAction();
        
        if ( isset( $_POST['role'] ) && $_POST['role'] != '' ) {
            $strRole = $_POST['role'];
            $objRole = User_Role::Table()->findByName( $_POST['role'] );

            if ( !is_object( $objRole ))
                throw new App_Exception ( 'Invalid User Role' );

            $objUser = $this->view->object;
            if ( ! $objUser->hasRole( $strRole ) ) {
                $objUserRole = User_UserRole :: Table()->createRow();
                $objUserRole->ucur_user_id = $objUser->getId();
                $objUserRole->ucur_role_id = $objRole->getId();
                $objUserRole->save();

                $objUser->cleanCache();
            }
        }
    }
}
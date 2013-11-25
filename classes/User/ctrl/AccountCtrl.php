<?php

class User_AccountCtrl extends App_DbTableCtrl
{
    public function getClassName()
    {
        return 'User_Account';
    }

    protected function  _filterField($strFieldName, $strFieldValue) {
        switch ( $strFieldName ) {
            case 'noroles':
                $this->_select->where( 'ucac_id NOT IN 
                       (SELECT ucur_user_id FROM '.User_UserRole::TableName().')', $strFieldValue );
		break;
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
        if ( $this->_isPost() && ! $this->_getParam( 'ucac_id' ) ) {
            $arrErrors = array();
            
            // if we are adding a new record, prevent adding with the same login
            $strNewLogin = $this->_getParam( 'ucac_login' );
            if ( $strNewLogin != "" ) {
                $objUser = User_Account::Table()->findByLogin( $strNewLogin );
                if ( is_object( $objUser ) ) {
                    $arrErrors[] = 'User with such login already exists'; 
                }
            }
            
            if ( count( $arrErrors ) > 0 ) {
                $this->view->lstErrors = $arrErrors;
                $this->view->object = User_Account::Table()->createRow();
                return;
            }
            
        }
        //add role if it is provided
        parent::editAction();
        
        $objUser = $this->view->object;
        if ( is_object( $this->view->object ) && $objUser->getId() ) {
            
            if ( $this->_hasParam( 'roles_list' ) ) {

                // when the list of roles is submitted directly with user form
                $arrExistingRoles = array();
                foreach ( $objUser->getRoles() as $objRole ) $arrExistingRoles[ $objRole->getId() ] = $objRole->getId();

                $arrNewIds = array();
                $arrIds = explode( ",", $this->_getParam( 'roles_list' ) );
                foreach ( $arrIds as $nRoleId ) {
                    $nRoleId = trim( $nRoleId );  if ( $nRoleId == '' ) continue; 

                    $arrNewIds [ $nRoleId ] = $nRoleId;
                    if ( !isset( $arrExistingRoles[ $nRoleId ] ) ) {
                        // need to add a role
                        $objUserRole = User_UserRole :: Table()->createRow();
                        $objUserRole->ucur_user_id = $objUser->getId();
                        $objUserRole->ucur_role_id = $nRoleId;
                        $objUserRole->save( false );
                    }
                }

                // walking through existing roles, delete IDs
                foreach ( $arrExistingRoles as $nRoleId ) {
                    if ( !isset( $arrNewIds[ $nRoleId ] )) {
                        // this role has to be deleted
                        $objUserRole = User_UserRole::Table()->findRole( $objUser->getId(), $nRoleId );
                        if ( is_object( $objUserRole ) ) $objUserRole->delete();
                    }
                }

                $objUser->cleanCache();
                $this->view->object = $objUser;

            } else if ( $this->_hasParam( 'role' ) && $this->_getParam('role') != '' ) {
                $strRole = $this->_getParam( 'role');
                $objRole = User_Role::Table()->findByName(  $strRole );

                if ( !is_object( $objRole ))
                    throw new App_Exception ( 'Invalid User Role' );

                $objUser = $this->view->object;
                if ( ! $objUser->hasRole( $strRole ) ) {
                    $objUserRole = User_UserRole :: Table()->createRow();
                    $objUserRole->ucur_user_id = $objUser->getId();
                    $objUserRole->ucur_role_id = $objRole->getId();
                    $objUserRole->save( false );

                    $objUser->cleanCache();
                }
            }
        } else {
            // Sys_Debug::dump( $this->view->lstErrors );    
        }
    }
}
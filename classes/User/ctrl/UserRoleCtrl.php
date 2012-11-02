<?php

class User_UserRoleCtrl extends App_DbTableCtrl
{

    public function setAction()
    {
        $nUser = $this->_getParam( 'ucac_id', 0 );
        $nRole = $this->_getParam( 'ucr_id',  0 );
        $nEnable = $this->_getBoolParam( 'enable', 1 );
        $this->_model = User_UserRole::Table();

        $selectResult = $this->_model->select()
                ->where( 'ucur_user_id = ?', $nUser )
                ->where( 'ucur_role_id = ?', $nRole );
        $this->_object = $this->_model->fetchRow( $selectResult );
        if ( !is_object( $this->_object ) ) {
            if ( $nEnable ) {
                $this->_object = $this->_model->createRow();
                $this->_object->ucur_user_id = $nUser;
                $this->_object->ucur_role_id = $nRole;
                $this->_object->save();
            }
        } else {
            if ( ! $nEnable ) {
                $this->_object->delete();
                $this->_object = null;
            }
        }

        $this->view->object = $this->_object;
    }
}
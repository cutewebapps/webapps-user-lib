<?php

class User_AccessListCtrl extends App_DbTableCtrl
{

    public function setAction()
    {
        $nResource = $this->_getParam( 'res_id', 0 );
        $nRole = $this->_getParam( 'ucr_id',  0 );
        $nEnable = $this->_getBoolParam( 'enable', 1 );
        $this->_model = User_AccessList::Table();

        $selectResult = $this->_model->select()
                ->where( 'ucal_resource_id = ?', $nResource )
                ->where( 'ucal_role_id = ?', $nRole );
        $this->_object = $this->_model->fetchRow( $selectResult );
        if ( !is_object( $this->_object ) ) {
            if ( $nEnable ) {
                $this->_object = $this->_model->createRow();
                $this->_object->ucal_resource_id = $nResource;
                $this->_object->ucal_role_id = $nRole;
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
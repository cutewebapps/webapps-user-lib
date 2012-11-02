<?php

// TODO: view base!
// TODO: all the cases are not thoroughly tested!

class User_Auth_CtrlPlugin extends App_Dispatcher_CtrlPlugin
{
 
    public function preDispatch()
    {
        $config = App_Application::getInstance()->getConfig();
        $arrUrlParams = $this->getDispatcher()->getUrlParams();

        $arrUserAreas =  array(
            'admin' => array(
                'theme' => 'admin',
                'section' => 'backend',
                'require_login' => 1,
            ),
        );
        if ( is_object( App_Application::getInstance()->getConfig()->user_area ))
            $arrUserAreas = App_Application::getInstance()->getConfig()->user_area->toArray();

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $strCurrentArea = '';
        $strNextParam = '';
        if ( isset( $arrUrlParams[1] ) && isset( $arrUserAreas[ $arrUrlParams[1] ] ) ) {
            $strCurrentArea = $arrUrlParams[1];
            if ( isset( $arrUrlParams[2] ))
                $strNextParam = $arrUrlParams[2];
        } else {
            if ( isset( $arrUrlParams[1] )) 
                $strNextParam = $arrUrlParams[1];
        }
        
        //if ( $strCurrentArea == 'admin' ) die;
        foreach ( $arrUserAreas as $strArea => $arrAreaProperties ) {


            if ( !isset($arrAreaProperties['theme']) )
                throw new App_Exception ( 'Theme was not specified for user area '.$strArea );
            if ( !isset($arrAreaProperties['section']) )
                throw new App_Exception ( 'Section was not specified for user area '.$strArea );

            if ( $strCurrentArea != $strArea ) continue;
            
// Sys_Io::out( 'CURRENT AREA: ' . $strCurrentArea . ' ' . $strArea );
            $strBaseAreaUrl = str_replace( '//','/', 
                    str_replace( '//','/',  App_Application::getInstance()->getConfig()->base . '/'.$strArea.'/'));

            $strSessionName = 'user_'.$strArea;
            $objSession = new App_Session_Namespace( $strSessionName );

            
            if ( isset( $objSession->user_id ) && ( $objSession->user_id != '' ) ) {

                /** @var $objUser User_Account */

                $tblUser = User_Account::Table();
                $selectUser = $tblUser->select()
                    ->where('ucac_id = ?',  $objSession->user_id );
                $objUser = $tblUser->fetchRow( $selectUser );
                if ( is_object( $objUser )) {
                    Sys_Global::set( 'USER_LOGIN', $objUser->ucac_login );
                    Sys_Global::set( 'USER_OBJECT', $objUser );
                    // Sys_Global::set( 'USER_ROLES',  $objUser->getRoles() );
                    // Sys_Debug::dumpDie( $objUser->getRoles() );
                }

                if ( $strNextParam == 'sign-out' ) {
                    $objSession->user_id = 0;
                    header( 'Location: '.$strBaseAreaUrl );
                    die();
                }
                
            } else {

                if ( isset( $_REQUEST[ 'errcode' ] ) )
                    Sys_Global::set( 'errcode', intval( $_REQUEST[ 'errcode' ] ) );

                if ( isset( $_REQUEST['login' ] ) &&
                     isset( $_REQUEST['password' ] ) ) {

                    $tblUser = User_Account::Table();
                    $selectUser = $tblUser->select()
                            ->where('ucac_login = ?', $_REQUEST['login'] )
                            ->where('ucac_password = ?', $_REQUEST['password'] );
                    $objUser = $tblUser->fetchRow( $selectUser );
                    if ( is_object( $objUser ) ) {
                        if ( $objUser->ucac_status == User_Account::ACTIVE) {
                            // TODO: check user role
                            if ( isset($arrAreaProperties['role']) ) {
                                if( !$objUser->hasRole( $arrAreaProperties['role'] ) ) {
                                    header( 'Location: '.$strBaseAreaUrl.'?errcode=3' ); die();
                                }
                            }
                            // Sys_Debug::dump( $objUser->getId() );
                            $objSession->user_id = $objUser->getId();

                        } else {
                            header( 'Location: '.$strBaseAreaUrl.'?errcode=2' ); die();
                        }
                        header( 'Location: '.$strBaseAreaUrl ); die();
                    } else {
                        header( 'Location: '.$strBaseAreaUrl.'?errcode=1' ); die();
                    }
                }

                if ( isset( $arrAreaProperties['require_login'] ) && $arrAreaProperties['require_login']  ==  1 ) {

                    $strViewClass = $config->default_renderer;
                    if ( $strViewClass ) {
                        $view = new $strViewClass();
                    } else {
                        $view = new App_View();
                    }

                    $arrThemes = $arrAreaProperties['theme'];
                    if ( !is_array( $arrThemes )) $arrThemes = array( $arrThemes );

                    $pathsTpl = array();
                    foreach( $arrThemes as $strTheme ) {
                        $pathsTpl[] = WC_APPLICATION_DIR . '/theme/' . $strTheme
                            .'/'.$arrAreaProperties['section'].'/auth.'.$view->getExtension();
                    }

                    $view->setPath( $pathsTpl );
                    $view->errcode = isset( $_REQUEST[ 'errcode' ] ) ? intval( $_REQUEST[ 'errcode' ] ) : 0;
                    echo $view->render();
                    die;
                }


            } 
            // end of pre-dispatch
            // - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        }
        return true;
    }

    public function postDispatch()
    {
    }
}
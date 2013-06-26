<?php

class User_Auth_CtrlPlugin extends App_Dispatcher_CtrlPlugin
{
 
    public function preDispatch()
    {
        if ( PHP_SAPI == "cli" ) return true;
    
        $config = App_Application::getInstance()->getConfig();
        $arrUrlParams = $this->getDispatcher()->getUrlParams();

        $arrUserAreas =  array(
            'admin' => array(
                'theme' => 'admin',
                'section' => 'backend',
                'require_login' => 1,
            ),
        );
        if ( is_object( App_Application::getInstance()->getConfig()->user->area ))
            $arrUserAreas = App_Application::getInstance()->getConfig()->user->area->toArray();

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
            $strUrlExt = '';
            $strRedirect = $strBaseAreaUrl;
            if ( $this->getParam( 'returnurl' ) != ""  ) {
                $strUrlExt = '&returnurl='.urlencode( $this->getParam( 'returnurl' ) );
                $strRedirect = $this->getParam( 'returnurl' );
            }
            
            
            $strSessionName = 'user_'.$strArea;
            $objSession = new App_Session_Namespace( $strSessionName );

            
            if ( isset( $objSession->user_id ) && ( $objSession->user_id != '' ) ) {

                /** @var $objUser User_Account */

                $tblUser = User_Account::Table();
                $selectUser = $tblUser->select()
                    ->where('ucac_id = ?',  $objSession->user_id );
                $objUser = $tblUser->fetchRow( $selectUser );
                if ( is_object( $objUser )  && $objUser->isActive()  ) {
                    Sys_Global::set( 'USER_LOGIN', $objUser->ucac_login );
                    Sys_Global::set( 'USER_OBJECT', $objUser );
                    // Sys_Global::set( 'USER_ROLES',  $objUser->getRoles() );
                    // Sys_Debug::dumpDie( $objUser->getRoles() );
		} else {
                    $strNextParam = 'sign-out';
                }

                if ( $strNextParam == 'sign-out' ) {
                    $objSession->user_id = 0;
                    header( 'Location: '.$strBaseAreaUrl );
                    die();
                }
                
            } else {

                if ( $this->getParam( 'errcode' ) )
                    Sys_Global::set( 'errcode', $this->getParam( 'errcode' ) );

                if ( $this->hasParam( 'login' ) && $this->hasParam('password') ) {

                    $tblUser = User_Account::Table();
                    $selectUser = $tblUser->select()
                            ->where('ucac_login = ?', $this->getParam( 'login' ) )
                            ->where('ucac_password = ?', $this->getParam( 'password' ) );
                    $objUser = $tblUser->fetchRow( $selectUser );
                    if ( is_object( $objUser ) ) {
                        if ( $objUser->ucac_status == User_Account::ACTIVE) {
                            
                            if ( isset($arrAreaProperties['role_forbidden']) ) {
                                if( $objUser->hasRole( $arrAreaProperties['role_forbidden'] ) ) {
                                    header( 'Location: '.$strBaseAreaUrl.'?errcode=3'.$strUrlExt ); die();
                                }
                            }
                            if ( isset($arrAreaProperties['role_required']) ) {
                                if( !$objUser->hasRole( $arrAreaProperties['role_required'] ) ) {
                                    header( 'Location: '.$strBaseAreaUrl.'?errcode=3'.$strUrlExt ); die();
                                }
                            }
                            // Sys_Debug::dump( $objUser->getId() );
                            $objSession->user_id = $objUser->getId();
                            header( 'Location: '.$strRedirect ); die;
                            
                        } else {
                            header( 'Location: '.$strBaseAreaUrl.'?errcode=2'.$strUrlExt ); die();
                        }
                        header( 'Location: '.$strBaseAreaUrl ); die();
                    } else {
                        header( 'Location: '.$strBaseAreaUrl.'?errcode=1'.$strUrlExt ); die();
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
                        $pathsTpl[] = CWA_APPLICATION_DIR . '/theme/' . $strTheme
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
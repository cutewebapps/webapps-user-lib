<?php

class User_Resource
{
    /** 
     * @param string $strResourceString
     * @return int resource ID
     */
    public static function parseString( $strResourceString, $strDelimiter = '/' ) 
    {
        $config = App_Application::getInstance()->getConfig()->user->resource;
        $arrPieces = explode( $strDelimiter, $strResourceString );
        if ( count( $arrPieces ) == 0 )
            throw new User_Acl_Exception( "Invalid resource string" );
        
        $strPath = '';
        $strLastPiece = $arrPieces[ count( $arrPieces ) - 1 ];
        if ( count( $arrPieces ) != 1 ) {
            unset( $arrPieces[ count( $arrPieces ) - 1 ] );
            
            foreach ( $arrPieces as $strPiece ) {
                $strPath .= '/'.$strPiece;
                if ( !is_object( $config->$strPiece ) ) {
                    throw new User_Acl_Exception( "Invalid resource string path ".$strPath.'::'.$strPiece );
                }
                $config = $config->$strPiece;
           }
        }
        return $config->$strLastPiece;
    }
}

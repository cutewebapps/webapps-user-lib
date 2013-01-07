<?php

class User_Update extends App_Update
{
    const VERSION = '0.1.2';
    public static function getClassName() { return 'User_Update'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }

    public function update()
    {
        $this->_install();
        if ( $this->isVersionBelow( '0.1.2' ) ) {
            
            $tblUsers = User_Account::Table();
            if ( !$tblUsers ->hasColumn( 'ucac_icon' ) ) {
                $tblUsers ->addColumn(  'ucac_icon','VARCHAR(255) DEFAULT \'\' NOT NULL');
            }
        }
        $this->_addDefaultRoles();
        $this->_addDefaultAccounts();
        $this->save( self::VERSION );
    }
    /**
     * @return array
     */
    public static function getTables()
    {
        return array(
            User_Option::TableName(),
            User_Account::TableName(),
            User_Role::TableName(),
            User_UserRole::TableName(),
            User_AccessList::TableName(),
        );
    }

    /**
     * @param array $arrProperties
     * @return User_Account
     */
    protected function _addDefaultAccount( $arrProperties )
    {
        $strLogin = $arrProperties['login'];
        $strPassword = $arrProperties['password'];

        $arrProps = array( 'first', 'last', 'email', 'phone' );
        $tblAccount = User_Account::Table();
        $selectAccount = $tblAccount->select()->where ( 'ucac_login = ?' , $strLogin );
        $objAccount = $tblAccount->fetchRow( $selectAccount  );
        if (is_object($objAccount)) return $objAccount;

        Sys_Io::out( 'adding default account: ' . $strLogin );
        $objAccount = $tblAccount->createRow();
        $objAccount->ucac_login    = $strLogin;
        $objAccount->ucac_password = $strPassword;
        foreach ( $arrProps as $strProperty )  {
            if ( isset( $arrProperties[ $strProperty ] ) && !is_array( $arrProperties[ $strProperty ] ) ) {
                $strField = 'ucac_'. $strProperty;
                $objAccount->$strField = $arrProperties[ $strProperty ];
            }
            // print_r ( $arrProperties );
        }
        $objAccount->save();
        return $objAccount;
    }

    /**
     * @return void
     */
    protected function _addDefaultRoles()
    {
        $tblRole = User_Role::Table();
        $cfgDefaultRoles = App_Application::getInstance()->getConfig()->user->role;
        if ( is_object($cfgDefaultRoles) ) {
            foreach ($cfgDefaultRoles as $strRole ) {
                $objRole = $tblRole->findByName( $strRole );
                if ( !is_object( $objRole )) {
                    Sys_Io::out( 'adding user role: '.$strRole );
                    $objRole = $tblRole->createRow();
                    $objRole->ucr_name = $strRole;
                    $objRole->ucr_date_added = date('Y-m-d H:i:s');
                    $objRole->save();
                }
            }
        } else {
            Sys_Io::out( 'no user roles' );
        }
    }

    /**
     * @return void
     */
    protected function _addDefaultAccounts()
    {
        $cfgDefaultAccounts = App_Application::getInstance()->getConfig()->user->list;

        if ( is_object($cfgDefaultAccounts) ) {
            $cfgDefaultAccount = null;
            $tblRole = User_Role::Table();
            $tblUserRole = User_UserRole::Table();
            
            /** @var $cfgDefaultAccount User_Account */
            foreach ($cfgDefaultAccounts as $cfgDefaultAccount) {
                $objAccount = $this->_addDefaultAccount( $cfgDefaultAccount->toArray() );

                if ( is_object( $cfgDefaultAccount->roles ) ) {
                    // add roles for a user...
                    $arrRoles = $cfgDefaultAccount->roles;
                    foreach( $arrRoles as $strRoleName ) {
                        $objRole = $tblRole->findByName( $strRoleName );
                        if (is_object( $objRole ) && !is_object(  $tblUserRole->findRole( $objAccount->getId(), $objRole->getId() ) ) ) {
                            $objUserRole = $tblUserRole->createRow();
                            $objUserRole->ucur_user_id = $objAccount->getId();
                            $objUserRole->ucur_role_id = $objRole->getId();
                            $objUserRole->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function _install()
    {
        //Sys_Io::out( 'Installing Users Management' );

        if (!$this->getDbAdapterRead()->hasTable('uc_account')) {
            Sys_Io::out( 'User Accounts created' );
            $this->getDbAdapterWrite()->addTableSql('uc_account', "
                `ucac_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ucac_login`    VARCHAR(255) NOT NULL DEFAULT '',
                `ucac_password` VARCHAR(255) NOT NULL DEFAULT '',
                `ucac_name`     VARCHAR(255) NOT NULL DEFAULT '',
                `ucac_first`    VARCHAR(255) NOT NULL DEFAULT '',
                `ucac_last`     VARCHAR(255) NOT NULL DEFAULT '',
                `ucac_email`    VARCHAR(255) NOT NULL DEFAULT '',
                `ucac_phone`    VARCHAR(255) NOT NULL DEFAULT '',
                `ucac_hash`     VARCHAR(255) NOT NULL DEFAULT '',
                `ucac_comment`  TEXT,
                `ucac_date_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `ucac_status` INT(10) UNSIGNED NOT NULL DEFAULT ". User_Account::ACTIVE . ",
                PRIMARY KEY (`ucac_id`),
                KEY i_name(`ucac_name`),
                UNIQUE KEY `ucac_login` (`ucac_login`),
                KEY `ucac_date_added` (`ucac_date_added`),
                KEY `ucac_status` (`ucac_status`)
                ");
      }
      
      if (!$this->getDbAdapterRead()->hasTable('uc_role')) {
                Sys_Io::out( 'User Roles created' );
                $this->getDbAdapterWrite()->addTableSql('uc_role', "
                `ucr_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ucr_name` VARCHAR(255) NOT NULL DEFAULT '',
                `ucr_date_added` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`ucr_id`)
                ");
      }
            
      if (!$this->getDbAdapterRead()->hasTable('uc_user_role')) {
                Sys_Io::out( 'User Account Roles created' );
                $this->getDbAdapterWrite()->addTableSql('uc_user_role', "
                `ucur_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ucur_user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                `ucur_role_id` INT(11) NOT NULL DEFAULT '0',
                `ucur_date_added` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`ucur_id`),
                KEY `ucur_user_id` (`ucur_user_id`),
                KEY `ucur_role_id` (`ucur_role_id`)
                ");
      }
            
      if (!$this->getDbAdapterRead()->hasTable('uc_access_list')) {
                Sys_Io::out( 'User Access Lists created' );
                $this->getDbAdapterWrite()->addTableSql('uc_access_list', "
                `ucal_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ucal_role_id`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
                `ucal_resource_id`  CHAR(30) NOT NULL DEFAULT '',
                `ucal_date_added`   DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`ucal_id`),
                KEY `ucal_role_id` (`ucal_role_id`),
                KEY `ucal_resource_id` (`ucal_resource_id`)
                ");
      }
            
      if (!$this->getDbAdapterRead()->hasTable('uc_option')) {
                Sys_Io::out( 'User options created' );
                $this->getDbAdapterWrite()->addTableSql('uc_option', "
                    
                `uco_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `uco_user_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0',
                `uco_key`        VARCHAR(255) NOT NULL DEFAULT '',
                `uco_value`      VARCHAR(255) NOT NULL DEFAULT '',
                
                PRIMARY KEY (`uco_id`),
                KEY `uco_user_id` (`uco_user_id`),
                KEY `uco_key` (`uco_key`)
                ");
        }
        
    }

}
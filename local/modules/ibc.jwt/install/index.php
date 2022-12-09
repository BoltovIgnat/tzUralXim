<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Ibc\Jwt\UserJwtTable;

Loc::loadMessages(__FILE__);

class ibc_jwt extends CModule
{
    public function __construct()
    {
        $arModuleVersion = array();
        
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        
        $this->MODULE_ID = 'ibc.jwt';
        $this->MODULE_NAME = '!Модуль jwt';
        $this->MODULE_DESCRIPTION =  'Модуль jwt';
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = 'ibc';
        $this->PARTNER_URI = 'ibc';
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installDB();
        $this->InstallEvents();

        copyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/ibc.jwt/assets/routes', $_SERVER["DOCUMENT_ROOT"] . '/local/routes', true, true);
    }

    public function doUninstall()
    {
        $this->uninstallDB();
        $this->UnInstallEvents();

        DeleteDirFilesEx("/local/routes/jwt-api.php");
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {

            UserJwtTable::getEntity()->createDbTable();

        }
    }

    public function uninstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
            $connection = Application::getInstance()->getConnection();

            $connection->dropTable(UserJwtTable::getTableName());

        }
    }

    function InstallEvents()
    {

    }

    function UnInstallEvents()
    {

    }
}

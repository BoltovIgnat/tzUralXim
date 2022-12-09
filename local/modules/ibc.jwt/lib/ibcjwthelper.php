<?php

namespace Ibc\Jwt;

use Bitrix\Main\Entity\Validator;
use Bitrix\Main\Localization\Loc;
use Ibc\Jwt\UserJwtTable;
use Bitrix\Main\Diag\Debug;

Loc::loadMessages(__FILE__);

class IbcJwtHelper
{

    public static $key = 'ibc';

    public static function bindJwtKey($params)
    {
        $infoparams = [];
        $infoparams['jwtkey'] = $params['jwtkey'];
        $infoparams['userid'] = $params['userid'];

        $income = UserJwtTable::createObject();
        $income->set('jwtkey', $params['jwtkey']);
        $income->set('userid', $params['userid']);


        $income->save();

    }

    public static function getJwtKeyByUserId($userid)
    {
        $dbEnums = UserJwtTable::getList([
            'select' => ['*'],
            'filter' => [
                'userid' => $userid,
            ]
        ]);

        while($arEnum = $dbEnums->fetch()) {
            $arResult = $arEnum;
        }

        return $arResult['jwtkey'];

    }

    public static function valideteJwtKey($jwtkey)
    {
        $dbEnums = UserJwtTable::getList([
            'select' => ['*'],
            'filter' => [
                'jwtkey' => $jwtkey,
            ],
            'count_total' => true,
        ]);

        if ($dbEnums->getCount() > 0){
            return true;
        }else{
            return false;
        }

    }
}

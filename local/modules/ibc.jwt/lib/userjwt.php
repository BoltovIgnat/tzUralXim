<?php

namespace Ibc\Jwt;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\Validator;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class UserJwtTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_user_jwt';
    }

    public static function getMap()
    {
        return array(
            new IntegerField('id', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'id',
            )),
            new StringField('jwtkey', array(
                'required' => false,
                'title' => 'Ключ jwt',
            )),
            new StringField('userid', array(
                'required' => false,
                'title' => 'Код пользователя',
            )),
            new StringField('ttl', array(
                'required' => false,
                'title' => 'Время жизни ключа',
            )),
            new StringField('date_update', array(
                'required' => false,
                'title' => 'Время обновления ключа',
            ))
        );
    }
}

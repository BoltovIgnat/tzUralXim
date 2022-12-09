<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Routing\RoutingConfigurator;
use Bitrix\Main\Loader;
Loader::includeModule("ibc.jwt");
use Ibc\Jwt\IbcJwtHelper;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

return function (RoutingConfigurator $routes) {
    // маршруты
    $routes->any('/local/rest/register', function () {
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $params = $request->getPostList()->toArray(); // массив post параметров

        $user = new CUser;
        $arFields = Array(
            "NAME"              => $params["NAME"],
            "LAST_NAME"         => $params["LAST_NAME"],
            "EMAIL"             => $params["EMAIL"],
            "LOGIN"             => $params["EMAIL"],
            "ACTIVE"            => "Y",
            "PASSWORD"          => $params["EMAIL"],
            "CONFIRM_PASSWORD"  => $params["EMAIL"],

        );

        $ID = $user->Add($arFields);

        if (intval($ID) > 0){
            $key = IbcJwtHelper::$key;

            $jwtkey = JWT::encode($params, $key, 'HS256');

            $params = [
                'userid' => $ID,
                'jwtkey' => $jwtkey,
            ];

            $res = IbcJwtHelper::bindJwtKey($params);

            return json_encode([
                'result' => "Пользователь успешно добавлен.",
                'jwtkey' => $jwtkey
            ]);

        }else{
            return json_encode([
                'result' => 'error',
                'msg' =>  $user->LAST_ERROR
            ]);
        }


    });

    $routes->any('/local/rest/auth', function () {
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $params = $request->getPostList()->toArray(); // массив post параметров
        global $USER;
        if (!is_object($USER)) $USER = new CUser;
        $arAuthResult = $USER->Login($params['LOGIN'], $params['PASSWORD'], "Y");
        if ($arAuthResult){
            $jwtkey = IbcJwtHelper::getJwtKeyByUserId($USER->GetID());
            return json_encode([
                'result' => "Пользователь успешно авторизовался.",
                'jwtkey' => $jwtkey
            ]);
        }else{
            return json_encode([
                'result' => 'error',
                'msg' =>  $arAuthResult
            ]);
        }


    });

    $routes->any('/local/rest/users/{id}', function ($id) {
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $params = $request->getPostList()->toArray();

        if (IbcJwtHelper::valideteJwtKey($params['jwtkey'])){

            $rsUser = CUser::GetByID($id);
            $arUser = $rsUser->Fetch();
            $arUser['USER_GROUPS'] = CUser::GetUserGroup($id);

            return json_encode([
                'result' => "Даные по пользователю.",
                'data' => $arUser
            ]);
        }else{
            return json_encode([
                'result' => 'error',
                'msg' =>  'Указан не корректный ключ'
            ]);
        }
    });

    $routes->any('/local/rest/users/', function () {
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $params = $request->getPostList()->toArray();

        if (IbcJwtHelper::valideteJwtKey($params['jwtkey'])){

            $cache = \Bitrix\Main\Application::getInstance()->getManagedCache();
            $cacheId = 'userpageid'.$params['PAGE'];
            $arResult = [];

            if ($cache->read(7200, $cacheId)) {
                $arResult = $cache->get($cacheId); // достаем переменные из кеша
            } else {

                $result = \Bitrix\Main\UserTable::getList(array(

                    'select' => array('*'), // выберем идентификатор и генерируемое (expression) поле SHORT_NAME

                    'order' => array(), // все группы, кроме основной группы администраторов,

                    'limit'   => 10,

                    'offset' => $params['PAGE']*10

                ));



                while ($arUser = $result->fetch()) {

                    $arResult[$arUser['ID']] = $arUser;
                    $arResult[$arUser['ID']]['USER_GROUPS'] = \Bitrix\Main\UserTable::getUserGroupIds($arUser['ID']);

                }

                $cache->set($cacheId, array("key" => $arResult)); // записываем в кеш
            }


            return json_encode([
                'result' => "Даные по пользователю.",
                'data' => $arResult
            ]);

        }else{
            return json_encode([
                'result' => 'error',
                'msg' =>  'Указан не корректный ключ'
            ]);
        }
    });
};
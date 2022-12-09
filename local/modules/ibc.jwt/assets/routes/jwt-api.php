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
        // сработает на любой тип запроса
        return '2)	/local/rest/auth';
    });

    $routes->any('/local/rest/users/{id}', function () {
        // сработает на любой тип запроса
        return '3)	/local/rest/users/{id}';
    });

    $routes->any('/local/rest/users/', function () {
        // сработает на любой тип запроса
        return '4)	/local/rest/users/';
    });
};
<?php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/phpqrcode/qrlib.php';

$data = json_decode(file_get_contents('php://input'), TRUE);
file_put_contents('filebots.txt', '$data: '.print_r($data, 1)."\n", FILE_APPEND);



$token = 'Ваш токен';


$message = $data['message']['text'];
$nameuse = (string) $data['message']['chat']['username'];
$members = null;
$buttonkey = null;
$params = array();

if ($nameuse === null or $nameuse === "") {
    $nameuse = (string) $data['message']['chat']['first_name'];
}

if ($message === "/start") {
    $members = "Используйте меню для выполнения действий";
}

if ($message === "/promo") {
    $json = file_get_contents('data_tyr.json');
    $tempArray = json_decode($json, true);
    if (isset($tempArray['@'.$nameuse]['promo']) and $tempArray['@'.$nameuse]['used'] === "useless") {
        $members = "Вы уже использовали свой промо QR-код";
    } else if (isset($tempArray['@'.$nameuse]['promo']) and $tempArray['@'.$nameuse]['used'] === "check") {
        $qrquery=array(
            'chat_id'=>$data['message']['chat']['id'],
            'photo'=>'https://web-pe.ru/app-php/Telegram_Tyr_Bots/qr/'.$tempArray['@'.$nameuse]['promo'].'.png',
            'caption'=>'<b>'.'Активируйте ваш промо QR-код'.'</b>',
            'parse_mode'=>"HTML"
        );

        file_get_contents('https://api.telegram.org/bot'.$token.'/sendPhoto?'.http_build_query($qrquery));

        exit();
    } else {
        $members = rand(9999999, 999999999);
        $tempArray['@'.$nameuse]['promo'] = $members;
        $tempArray['@'.$nameuse]['used'] = 'check';
        $jsonData = json_encode($tempArray, JSON_PRETTY_PRINT);
        file_put_contents('data_tyr.json', $jsonData);
        QRcode::png((string) $members, __DIR__ . '/qr/'.$members.'.png', "H" , 8, 2);
        $im = imagecreatefrompng(__DIR__ . '/qr/'.$members.'.png');

        $width = imagesx($im);
        $height = imagesy($im);

        $dst = imagecreatetruecolor($width, $height);
        imagecopy($dst, $im, 0, 0, 0, 0, $width, $height);
        imagedestroy($im);

        $logo = imagecreatefrompng(__DIR__ . '/assert/logo.png');
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);

        $new_width = $width / 3;
        $new_height = $logo_height / ($logo_width / $new_width);

        $x = ceil(($width - $new_width) / 2);
        $y = ceil(($height - $new_height) / 2);

        imagecopyresampled($dst, $logo, $x, $y, 0, 0, $new_width, $new_height, $logo_width, $logo_height);
        imagepng($dst, __DIR__ . '/qr/'.$members.'.png' );
        imagedestroy($dst);



        $qrquery=array(
            'chat_id'=>$data['message']['chat']['id'],
            'photo'=>'https://web-pe.ru/app-php/Telegram_Tyr_Bots/qr/'.$members.'.png',
            'caption'=>'<b>c'.$members.'</b>',
            'parse_mode'=>"HTML"
        );

        file_get_contents('https://api.telegram.org/bot'.$token.'/sendPhoto?'.http_build_query($qrquery));

        exit();

    }


} else if ($message === "Просмотреть список сотрудников") {
    $json = file_get_contents('data_req.json');
    $json_data = json_decode($json, true);
    foreach ($json_data as $key => $value) {
        $members = $members . "<b>" . " " . $key . ": " . $value['office'] . ";" . "</b>" . "\n";
    }

} else if ($message === "Проверить купон") {
    $buttapp = array();
    $buttapp["chat_id"] = $data['message']['chat']['id'];
    $buttapp["parse_mode"] = "HTML";
    $buttapp["text"] = "<b>Запустите приложение для сканирования QR-кода</b>";
    $buttapp["reply_markup"] = json_encode(array(
        'inline_keyboard' => array(
            array(
                array(
                    'text' => 'Проверить QR',
                    'url'=>'https://t.me/TirLimonkaBot/qrcodelimonka',
                ),
            )
        )
    ), JSON_PRETTY_PRINT);

    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?'.http_build_query($buttapp));

    exit();

} else if ($message === "Добавить сотрудника") {
    $jsonAD = file_get_contents('data_req.json');
    $json_dataAD = json_decode($jsonAD, true);
    if ($json_dataAD['@'.$nameuse]["status"] !== "in progress add worker") {
        $json_dataAD['@'.$nameuse]["status"] = "in progress add worker";
        $members = "Введите ник сотрудника без @";
    } else {
        $members = "Вы уже добавляете сотрудника";
    }
    $jsonADSend = json_encode($json_dataAD, JSON_PRETTY_PRINT);
    file_put_contents('data_req.json', $jsonADSend);

} else if ($message === "Отмена") {
        $jsonAD = file_get_contents('data_req.json');
    $json_dataAD = json_decode($jsonAD, true);
    $json_dataAD['@'.$nameuse]["status"] = "actives";
            $members = "Действие отменено";
            $jsonADSend = json_encode($json_dataAD, JSON_PRETTY_PRINT);
    file_put_contents('data_req.json', $jsonADSend);
} else if ($message === "Удалить сотрудника") {
    $jsonAD = file_get_contents('data_req.json');
    $json_dataAD = json_decode($jsonAD, true);
    if ($json_dataAD['@'.$nameuse]["status"] !== "in progress remove worker") {
        $json_dataAD['@'.$nameuse]["status"] = "in progress remove worker";
        $members = "Введите ник сотрудника без @";
    } else {
        $members = "Вы уже удаляете сотрудника";
    }
    $jsonADSend = json_encode($json_dataAD, JSON_PRETTY_PRINT);
    file_put_contents('data_req.json', $jsonADSend);

} else if ($message === "/login") {
    $jsonAD = file_get_contents('data_req.json');
    $json_dataAD = json_decode($jsonAD, true);
    if ($json_dataAD['@'.$nameuse]["office"] === "admin") {

        $members = "Hello! Admin " . "@" . $nameuse;

        $keyboard=array(
            "resize_keyboard"=>true,
            "keyboard"=>array(
                array(
                    array(
                        'text' => 'Проверить купон',
                    ),
                    array(
                        'text' => 'Просмотреть список сотрудников',
                    ),
                ),
                array(
                    array(
                        'text' => "Добавить сотрудника"
                    ),
                    array(
                        'text' => "Удалить сотрудника"
                    )
                ),
                array(
                    array(
                        'text' => "Отмена"
                    )
                )
            ),

        );

        $params["reply_markup"] = json_encode($keyboard, JSON_PRETTY_PRINT);

    } else if ($json_dataAD['@'.$nameuse]["office"] === "employee") {
        $members = "Hello! Worker " . " @" . $nameuse;
        $keyboard=array(
            "resize_keyboard"=>true,
            "keyboard"=>array(
                array(
                    array(
                        'text' => 'Проверить купон',
                    )
                )
            ),

        );

        $params["reply_markup"] = json_encode($keyboard, JSON_PRETTY_PRINT);
    } else {
        $members = "Hello!" . " @" . $nameuse;
        $params["reply_markup"] = json_encode(
            array(
                "resize_keyboard"=>true,
                "inline_keyboard"=>array(
                    array(
                        array(
                            'text' => 'Перейти на сайт www.tirlimonka.ru',
                            'url'=>'https://tirlimonka.ru/',
                        ),
                    )
                )
            ), JSON_PRETTY_PRINT
        );

    }

} else {
    $jsonAD = file_get_contents('data_req.json');
    $json_dataAD = json_decode($jsonAD, true);

    switch ($json_dataAD['@'.$nameuse]["status"]) {
        case "in progress add worker":
            $json_dataAD["@".$message]["temp_name"] = $message;
            $json_dataAD['@'.$nameuse]["last_message_user"] = $message;
            $json_dataAD['@'.$nameuse]["status"] = "in progress save name worker";
            $members = "Имя сотрудника зарегестрировано. Введите код должности!\n 1 - admin\n 2 - employee\n 3 - user";
            break;
        case "in progress save name worker":
            $out_message = $message;
            switch ($out_message) {
                case "1":
                    $message = "admin";
                break;
                case "2":
                    $message = "employee";
                break;
                case "3":
                    $message = "user";
                break;
                default:
                    $message = "unpost";
            }
            if ($message === "unpost") {
                $members = "Ошибка в должности или в имени сотрудника!";
                unset($json_dataAD["@".$json_dataAD['@'.$nameuse]["last_message_user"]]);
                $json_dataAD['@'.$nameuse]["status"] = "actives";
            } else {
            $json_dataAD["@".$json_dataAD['@'.$nameuse]["last_message_user"]]["office"] = $message;
            $json_dataAD["@".$json_dataAD['@'.$nameuse]["last_message_user"]]["status"] = "actives";
            $json_dataAD["@".$json_dataAD['@'.$nameuse]["last_message_user"]]["last_message_user"] = "empty";
            $json_dataAD["@".$json_dataAD['@'.$nameuse]["last_message_user"]]["last_message_bot"] = "empty";
            $json_dataAD['@'.$nameuse]["status"] = "actives";
            $members = "Сотрудник зарегестрирован";
            break;
            }
            case "in progress remove worker":
            $members = "Сотрудник удален!";
                unset($json_dataAD["@".$message]);
                $json_dataAD['@'.$nameuse]["status"] = "actives";
            break;
    }

    $jsonADSend = json_encode($json_dataAD, JSON_PRETTY_PRINT);
    file_put_contents('data_req.json', $jsonADSend);

}

$params["chat_id"] = $data['message']['chat']['id'];
$params["parse_mode"] = "HTML";
$params["text"] = "<b>".$members."</b>";


file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?'.http_build_query($params));

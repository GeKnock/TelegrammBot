<?php

$request = $_GET["qr"];
$responces = "Неверный QR-код";

$json = file_get_contents('data_tyr.json');
$tempArray = json_decode($json, true);

foreach ($tempArray as $key => &$value) {
        if ((string) $value['promo'] === $request) {
            
             if ((string) $value['used'] === "check") {
                $value['used'] = "useless";
                $responces = "Промокод активирован";
                
                break;
            } else if ($value['used'] === "useless") {
            
            $responces = "Промокод уже активирован!";
            break;

            }
        }
    }
    unset($value);

$jsonData = json_encode($tempArray, JSON_PRETTY_PRINT);
file_put_contents('data_tyr.json', $jsonData);
 

echo $responces;
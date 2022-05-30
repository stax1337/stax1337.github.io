<?php

const TOKEN = '5524453189:AAF4ae4hvYHsQblD-l3hUDhlCDh2peIIr6Q';
const BASE_URL = 'https://api.telegram.org/bot' . TOKEN . '/';

const ABOBA_TOKEN = '8d98ae3f-c492-4a38-9450-ac87271d7311';
const ABOBA_URL = 'https://holidayapi.com/v1/holidays?pretty&language=ru&country=ru&key=' . ABOBA_TOKEN;

$update = json_decode(file_get_contents('php://input'));

file_put_contents(__DIR__ . '/logs.txt', print_r($update, 1), FILE_APPEND); ЛОГ - ФАЙЛ

$chat_id = $update->message->chat->id ?? '';
$text = $update->message->text ?? '';

if ($text == '/start') {
    $res = send_request('sendMessage', [
        'chat_id' => $chat_id,
        'text' => 'Привет! Я - бессполезный бот. Я могу подсказать, какой праздник выпадет на определенную дату. Просто введи дату в формате Д-М-ГГГГ. Например, 31-1-2020',
    ]);
} elseif (preg_match("#^([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})$#", $text, $matches)) {
    $holidays = json_decode(
        file_get_contents(
            HOLIDAY_URL . "&year={$matches[3]}&month={$matches[2]}&day={$matches[1]}",
            false,
            stream_context_create(['http' => ['ignore_errors' => true]])
        )
    );
    if (isset($holidays->error)) {
        $result = $holidays->error;
    } elseif (!empty($holidays->holidays)) {
        $result = '';
        foreach ($holidays->holidays as $holiday) {
            $result .= $holiday->name . PHP_EOL;
        }
    } else {
        $result = 'В этот день праздников нет...';
    }

    $res = send_request('sendMessage', [
        'chat_id' => $chat_id,
        'text' => $result,
    ]);
} else {
    $res = send_request('sendMessage', [
        'chat_id' => $chat_id,
        'text' => 'Не понял...',
    ]);
}

function send_request($method, $params = [])
{
    if (!empty($params)) {
        $url = BASE_URL . $method . '?' . http_build_query($params);
    } else {
        $url = BASE_URL . $method;
    }
    return json_decode(file_get_contents($url));
}

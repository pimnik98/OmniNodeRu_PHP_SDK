<?php
# Подключаем файл конфигурации
require_once 'config.php';
# Подключаем SDK
require_once 'OmniNodeRu.php';
$omni = new OmniNodeRu(OMNINODE_IN, OMNINODE_OUT);

# Пишем сообщение
$text = "Как меня зовут?";

# Добавляем историю
$history = [
  ["role" => "user", "content" => "Привет, меня зовут Никита."],
  ["role" => "assistant", "content" => "Привет, Никита! Рад знакомству. Чем могу помочь?"]
];

# Делаем запрос
$RequestID = $omni->Ask($text, $history);

if ($RequestID){
    # Запрос выполнен успешно. Получен индификатор
    echo "The request has been accepted. Request Identifier: ".$RequestID;
} else {
    # Произошла ошибка. Если присутствует дополнительная информация по ошибке она будет записана в лог сервера.
    echo "Request registration error! The requests may have ended or the server is not responding.";
}
<?php
# Подключаем файл конфигурации
require_once 'config.php';
# Подключаем SDK
require_once 'OmniNodeRu.php';
$omni = new OmniNodeRu(OMNINODE_IN, OMNINODE_OUT);
# Тут нужно указывать индификатор который вы получили ранее, для ручной проверки
$RequestID = "ID_ПОЛУЧЕННЫЙ_РАНЕЕ";
# Отправляем запрос на сервер
$status = $omni->Check($RequestID);
# Получаем ответ
echo "Текущий статус: " . $status["status"]."<br>Ответ: ".$status["response"]; 
<?php
# Подключаем файл конфигурации
require_once 'config.php';
# Подключаем SDK
require_once 'OmniNodeRu.php';
$omni = new OmniNodeRu(OMNINODE_IN, OMNINODE_OUT);

$omni->Handler(function($RequestID, $Result) {
  // Этот код выполнится только если запрос подлинный и подпись верна
  
  // Пример обработки: сохранение в файл или базу данных
  $logMessage = "Запрос ID: $RequestID успешно выполнен.\nОтвет: " . print_r($Result, true) . "\n";
  file_put_contents('results.log', $logMessage, FILE_APPEND);
});
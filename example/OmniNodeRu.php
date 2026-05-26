<?php
class OmniNodeRu {
    private $InToken    = "";   ///! Входящий токен
    private $OutToken   = "";   ///! Исходящий токен
    private $Server     = "";   ///! Адрес ноды

    public function __construct($InToken, $OutToken){
        $this->InToken  = $InToken;
        $this->OutToken = $OutToken;
        $this->Server   = "https://OmniNoder.Ru/api/";
    }
    
    private function Query($method, $params){
        $url = $this->Server.$method."/";
        $params["token"] = $this->InToken;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        // Выполнение запроса
        $exec = curl_exec($ch);
        $response = json_decode($exec, true);
        curl_close($ch);
        return $response;
    }

    /**
     * Сделать запрос к нейронке
     */
    public function Ask($message, $history = array()){
        $params = array();
        $params["promt"] = $message;
        $params["history"] = json_encode($history);

        $Ask = $this->Query("send", $params);

        return isset($Ask["request_id"])?$Ask["request_id"]:NULL;
    }

    /**
     * Проверить состояние запроса
     */
    public function Check($RequestID){
        $params = array();
        $params["request_id"] = $RequestID;

        $Ask = $this->Query("check", $params);

        return isset($Ask["status"])?$Ask["status"]:NULL;
    }

    /**
     * Обработка ответов от сервиса
     */
    public function Handler($callback){
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        $RequestID = null;
         if (!is_array($data)) {
            $this->SendResponseCode(400);
            exit('Invalid JSON payload');
        }

        $signature = isset($data['signature']) ? $data['signature'] : '';
        unset($data['signature']); 
        $payload = json_encode($data); 
        $validSign = hash_hmac('sha256', $payload, $this->OutToken);

        if (!$this->SafeEquals($validSign, $signature)) {
            $this->SendResponseCode(403);
            exit('Invalid signature');
        }

        if (isset($data['request_id'])) {
            $RequestID = $data['request_id'];
        }

        $result = isset($data['response']) ? $data['response'] : null;

        if (is_callable($callback)) {
            call_user_func($callback, $RequestID, $result);
        } else {
            $this->SendResponseCode(500);
            exit('Callback function is not callable');
        }


    }

    /**
     * Отправка HTTP-кода ответа
     */
    private function SendResponseCode($code) {
        if (function_exists('http_response_code')) {
            http_response_code($code);
        } else {
            $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
            $texts = array(
                400 => 'Bad Request',
                403 => 'Forbidden',
                500 => 'Internal Server Error'
            );
            $text = isset($texts[$code]) ? $texts[$code] : '';
            header($protocol . ' ' . $code . ' ' . $text);
        }
    }

    /**
     * Безопасное сравнение строк 
     * Предотвращает Timing Attacks
     */
    private function SafeEquals($KnownString, $UserString) {
        if (function_exists('hash_equals')) {
            return hash_equals($KnownString, $UserString);
        }

        $knownLen = strlen($KnownString);
        $userLen = strlen($UserString);

        if ($knownLen !== $userLen) {
            return false;
        }

        $res = 0;
        for ($i = 0; $i < $knownLen; $i++) {
            $res |= (ord($KnownString[$i]) ^ ord($UserString[$i]));
        }

        return $res === 0;
    }
}

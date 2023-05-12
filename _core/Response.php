<?php 
namespace DafCore;

class Response {
    private $statusCode;
    private $reasonPhrase;
    private $headers;
    private $body;

    public function __construct($statusCode = 200, $reasonPhrase = null) {
        $this->status($statusCode, $reasonPhrase);
        $this->headers = [];
        $this->body = '';
    }

    public function reset() {
        $this->statusCode = 200;
        $this->reasonPhrase = null;
        $this->headers = [];
        $this->body = '';
    }

    public function json_stringify($obj){
        try
        {
            return json_encode($obj, JSON_THROW_ON_ERROR);
        }
        catch (\Throwable $e)
        {
            return "Throwable on json stringify: " . $e->getMessage() . PHP_EOL;
        }
    }

    public function status($statusCode, $reasonPhrase = null) : self {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase ? $reasonPhrase : $this->getHttpStatusReasonPhrase($statusCode);
        return $this;
    }

    public function setStatus($statusCode, $reasonPhrase = null){
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase ? $reasonPhrase : $this->getHttpStatusReasonPhrase($statusCode);
        
        http_response_code($this->statusCode);
        header(sprintf('HTTP/1.1 %d %s', $this->statusCode, $this->reasonPhrase), true, $this->statusCode);
    }

    public function getStatus() {
        return $this->statusCode;
    }

    public function getReasonPhrase() {
        return $this->reasonPhrase;
    }

    public function setHeader($name, $value) {
        $this->headers[$name] = $value;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function send($obj = null, $json_stringify = false) {
        $this->body = !empty($obj) ? $obj : " "; 
        
        if($obj && $json_stringify){
            $this->body = $this->json_stringify($obj);
            $this->setHeader("Content-Type", "application/json; charset=utf-8");
        }else if($obj){
            $this->body = $obj;
        }

        $this->setStatus($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        echo $this->body;

        $this->reset();
    }

    private function getHttpStatusReasonPhrase($statusCode) {
        switch ($statusCode) {
            case 100:
                return 'Continue';
            case 101:
                return 'Switching Protocols';
            case 200:
                return 'OK';
            case 201:
                return 'Created';
            case 202:
                return 'Accepted';
            case 203:
                return 'Non-Authoritative Information';
            case 204:
                return 'No Content';
            case 205:
                return 'Reset Content';
            case 206:
                return 'Partial Content';
            case 300:
                return 'Multiple Choices';
            case 301:
                return 'Moved Permanently';
            case 302:
                return 'Found';
            case 303:
                return 'See Other';
            case 304:
                return 'Not Modified';
            case 305:
                return 'Use Proxy';
            case 307:
                return 'Temporary Redirect';
            case 400:
                return 'Bad Request';
            case 401:
                return 'Unauthorized';
            case 402:
                return 'Payment Required';
            case 403:
                return 'Forbidden';
            case 404:
                return 'Not Found';
            case 405:
                return 'Method Not Allowed';
            case 406:
                return 'Not Acceptable';
            case 407:
                return 'Proxy Authentication Required';
            case 408:
                return 'Request Timeout';
            case 409:
                return 'Conflict';
            case 410:
                return 'Gone';
            case 411:
                return 'Length Required';
            case 412:
                return 'Precondition Failed';
            case 413:
                return 'Request Entity Too Large';
            case 414:
                return 'Request-URI Too Long';
            case 415:
                return 'Unsupported Media Type';
            case 416:
                return 'Requested Range Not Satisfiable';
            case 417:
                return 'Expectation Failed';
            case 500:
                return 'Internal Server Error';
            case 501:
                return 'Not Implemented';
            case 502:
                return 'Bad Gateway';
            case 503:
                return 'Service Unavailable';
            case 504:
                return 'Gateway Timeout';
            case 505:
                return 'HTTP Version Not Supported';
            default:
                return 'Internal Server Error';
        }
    }

    public function ok($obj = null){
        $this->status(200);
        if($obj)
        {
            if(!(is_array($obj) || is_object($obj))){
                $this->send($obj);
            }
            else $this->send($this->json_stringify($obj));
        }
        else $this->send();
    }

    public function created($obj = null){
        $this->status(201);
        if($obj)
        {
            if(!(is_array($obj) || is_object($obj))){
                $this->send($obj);
            }
            else $this->send($this->json_stringify($obj));
        }
        else $this->send();
    }
    
    public function noContent(){
        $this->status(204)->send();
    }

    public function badRequest($msg = null){
        $this->status(400)->send($msg);
    }

    public function notFound($msg = null){
        $this->status(404)->send($msg);
    }

    public function forbidden($msg = null){
        $this->status(403)->send($msg);
    }

    public function unauthorized($msg = null){
        $this->status(401)->send($msg);
    }
}




// public function ok($obj = null){
//     $this->status(200);
//     if($obj)
//     {
//         if(!(is_array($obj) || is_object($obj))){
//             $this->send($obj);
//         }
//         else $this->send($this->json_stringify($obj));
//     }
//     else $this->send();
// }

// public function created($obj = null){
//     $this->status(201);
//     if($obj)
//     {
//         if(!(is_array($obj) || is_object($obj))){
//             $this->send($obj);
//         }
//         else $this->send($this->json_stringify($obj));
//     }
//     else $this->send();
// }

// public function noContent(){
//     $this->status(204)->send();
// }

// public function badRequset($msg = null){
//     $this->status(400)
//     ->send(
//         "<h1>Bad Requset</h1>".
//         ($msg ? "<b>Error:</b><p>$msg</p>" : "")
//     );
// }

// public function notFound($msg = null){
//     $this->status(404)
//     ->send(
//         "<h1>Not Found</h1>".
//         ($msg ? "<b>Error:</b><p>$msg</p>" : "")
//     );
// }

// public function forbidden($msg = null){
//     $this->status(403)
//     ->send(
//         "<h1>Forbidden</h1>".
//         ($msg ? "<b>Error:</b><p>$msg</p>" : "")
//     );
// }

// public function unauthorized($msg = null){
//     $this->status(401)
//     ->send(
//         "<h1>Unauthorized</h1>".
//         ($msg ? "<b>Error:</b><p>$msg</p>" : "")
//     );
// }



?>
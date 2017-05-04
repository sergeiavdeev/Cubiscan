<?php

namespace Avdeev\Quantronix;

class Cubiscan{
    
    private $ip;
    private $port;
    private $socket;
    private $measureMsg;
    private $measureMsgLength;
    private $errors;
    private $timeout;
            
    function __construct($ip, $port, $timout = 5) {
        
        $this->ip = $ip;
        $this->port = $port;
        $this->measureMsg = chr(0x02).chr(0x4D).chr(0x03).chr(0x0D).chr(0x0A);
        $this->measureMsgLength = strlen($this->measureMsg);
        $this->errors = Array(); 
        $this->timeout = $timout;
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if($this->socket < 0){            
            $this->errors[] = "Create socket error!";
        }
    }
    
    public function measure() {
        
        $result = Array("isError" => true, 
               "errors" => Array(),
               "data" => Array());
        
        socket_set_nonblock($this->socket);
        
        $time = time();
        $res = true;
        
        while (!@socket_connect($this->socket, $this->ip, $this->port)) {
            $err = socket_last_error($this->socket);
            
            if($err === SOCKET_EISCONN) {                
                break;
            }
                        
            if ((time() - $time) >= $this->timeout) {
                
                $res = false;
                break;
            }
                        
            usleep(100000);
        }
        
        socket_set_block($this->socket);
        
        if ($res !== false) { 
            
            $res= socket_write($this->socket, $this->measureMsg, $this->measureMsgLength);
            if ($res !== false) {
                
                $res= socket_read($socket, 128, PHP_NORMAL_READ);
                
                if ($res !== false) {
                    
                    $result["data"] = Array(
                        "length" => (float)substr($out, 12, 5),
                        "width" => (float)substr($out, 19, 5),
                        "height" => (float)substr($out, 26, 5),
                        "weight" => (float)substr($out, 35, 6),
                        "dimWeight" => (float)substr($out, 43, 6),
                        "volWeight" => (float)substr($out, 43, 6)
                        );
                    
                    $result["isError"] = false;
                } else {
                    
                    $this->errors[] = "Socket read error!";                                        
                }
            } else {
                
                $this->errors[] = "Socket write error!";                                
            }
        } else { 
            
            $this->errors[] = "Socket connet error!";                        
        }
                
        socket_close($this->socket);
        
        $result["errors"] = $this->errors;
        
        return $result;
    }
}


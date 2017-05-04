<?php

namespace Avdeev\Quantronix;

/**
 * @author avdeev.sa
 * Cubiscan Ethernet API
 *
 */
class Cubiscan{
    
    private $ip;
    private $port;
    private $socket;
    private $measureMsg;
    private $measureMsgLength;
    private $errors;
    private $timeout;
    
    /**
     * 
     * @param string $ip
     * @param number $port
     * @param number $timout deault value 5s
     */
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
    
    /**
     * 
     * @return array (
     *     'isError' => boolean, 
     *     'errors' => array(string),
     *     'data' => array(
     *         'length' => float, 
     *         'width' => float, 
     *         'height' => float, 
     *         'weight' => float,
     *         'dimWeight' => float))
     */
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
                        
            usleep(1000);
        }
        
        socket_set_block($this->socket);
        
        if ($res !== false) { 
            
            $res= socket_write($this->socket, $this->measureMsg, $this->measureMsgLength);
            if ($res !== false) {
                
                $res= socket_read($this->socket, 128, PHP_NORMAL_READ);
                
                if ($res !== false) {
                    
                    $result["data"] = Array(
                        "length" => (float)substr($res, 12, 5),
                        "width" => (float)substr($res, 19, 5),
                        "height" => (float)substr($res, 26, 5),
                        "weight" => (float)substr($res, 35, 6),
                        "dimWeight" => (float)substr($res, 43, 6),
                        "volWeight" => (float)substr($res, 43, 6)
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


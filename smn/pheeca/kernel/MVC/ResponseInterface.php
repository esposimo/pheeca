<?php
namespace smn\pheeca\kernel\MVC;
/**
 * Description of ResponseInterface
 *
 * @author Simone Esposito
 */
interface ResponseInterface {
    
    
    public function removeHeader($headerName);
    
    public function setHeader($headerName, $headerValue = '');

    public function getHeader($headerName);
    
    public function sendHeader();

    public function setHttpCode($code = 200);

    public function getHttpCode();
}

<?php
/**
 * Http Standards for check http status
 *
 * @author Mithun Mandal <mithun.mandal@quikr.com>
 */
namespace jobs\components\http;

/**
 * Http Standard for Rest API's
 *
 * @author Mithun Mandal <mithun.mandal@quikr.com>
 */
class Http {
    /**
     * Http Success Code in Array
     * @var Array 
     */
    static private $sucesscode = array(
        200=>"OK",
        201=>"Created",
        202=>"Accepted",
        203=>"Non-Authoritative Information",
        204=>"No Content",
        205=>"Reset Content",
        206=>"Partial Content",
    );
    
    /**
     * Http Information Code in Array
     * @var Array 
     */
    static private $informcode = array(
        100=>"Continue",
        101=>"Switching Protocols"
    );
    
    /**
     * Http Redirect Code in Array
     * @var Array 
     */
    static private $redirectcode = array(
        300=>"Multiple Choices",
        301=>"Moved Permanently",
        302=>"Found",
        303=>"See Other",
        304=>"Not Modified",
        305=>"Use Proxy",
        306=>"(Unused)",
        307=>"Temporary Redirect",
    );
    
    /**
     * Http Error Code in Array
     * @var Array 
     */
    static private $errorcode = array(
        400=>"Bad Request",
        401=>"Unauthorized",
        402=>"Payment Required",
        403=>"Forbidden",
        404=>"Not Found",
        405=>"Method Not Allowed",
        406=>"Not Acceptable",
        407=>"Proxy Authentication Required",
        408=>"Request Timeout",
        409=>"Conflict",
        410=>"Gone",
        411=>"Length Required",
        412=>"Precondition Failed",
        413=>"Request Entity Too Large",
        414=>"Request-URI Too Long",
        415=>"Unsupported Media Type",
        416=>"Requested Range Not Satisfiable",
        417=>"Expectation Failed",
    );
    
    /**
     * Http Server Error Code in Array
     * @var Array 
     */
    static private $servererrorcode = array(
        500=>"Internal Server Error",
        501=>"Not Implemented",
        502=>"Bad Gateway",
        503=>"Service Unavailable",
        504=>"Gateway Timeout",
        505=>"HTTP Version Not Supported",
    );
    
    /**
     * Check whether request is success
     * @param Integer $code http status code
     * @return Boolean
     */
    static public function checkSuccess($code){
        return isset(self::$sucesscode[$code]) ? true : false;
    }
    
    /**
     * Check whether request is redirected
     * @param Integer $code http status code
     * @return Boolean
     */
    static public function checkRedirect($code){
        return isset(self::$redirectcode[$code]) ? true : false;
    }
    
    /**
     * Check whether request is error
     * @param Integer $code http status code
     * @return Boolean
     */
    static public function checkError($code){
        return isset(self::$errorcode[$code]) ? true : isset(self::$servererrorcode[$code]) ? true : false;
    }
    
    /**
     * Check whether request is information
     * @param Integer $code http status code
     * @return Boolean
     */
    static public function checkInfo($code){
        return isset(self::$informcode[$code]) ? true : false;
    }
}
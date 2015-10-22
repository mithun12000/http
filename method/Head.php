<?php
/**
 * Head http call wrapper
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
namespace mithun\http\method;
use mithun\http\lib\Curl;

/**
 * Curl Wrapper for Rest API's
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class Head extends Curl {
    /**
     * Main Function for Processing Curl
     * @param String $url Target Url
     * @param Array $data Post values
     * @param boolean $jsonpost Whether JSON post or not
     * @param boolean $prepare whether prepare or not
     * @return String 
     * @throws \Exception
     */
    public function run($url, $data = array(), $jsonpost = false, $prepare = false) {
        return parent::run($url, Curl::HEAD, $data, $jsonpost, $prepare);
    }
    
    /**
     * method to build request data for this call
     * @param String $method not required but auto called
     * @param Array $data request data as Array
     * @param Boolean $json is this send json data?
     */
    private function buildRequest($method, $data,$json){
        $this->defaults();            
        $this->setOption(CURLOPT_NOBODY, true);
        $this->setOption(CURLOPT_HEADER, true);        
    }
}

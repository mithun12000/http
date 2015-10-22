<?php
/**
 * Put http call wrapper
 *
 * @author Mithun Mandal <mithun.mandal@quikr.com>
 */
namespace jobs\components\http;

/**
 * Curl Wrapper for Rest API's
 *
 * @author Mithun Mandal <mithun.mandal@quikr.com>
 */
class Put extends Curl{
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
        return parent::run($url, Curl::PUT, $data, $jsonpost, $prepare);
    }
    
    /**
     * method to build request data for this call
     * @param String $method not required but auto called
     * @param Array $data request data as Array
     * @param Boolean $json is this send json data?
     */
    private function buildRequest($method, $data,$json){
        $this->defaults();            
        $this->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
        if($json){
            $jsonData = json_encode($data);
            $this->setOption(CURLOPT_POSTFIELDS,$jsonData);
            $this->setOption(CURLOPT_HTTPHEADER, array(
                                        'Content-Type: application/json',    
                                        'Content-Length: '.strlen($jsonData)
                                    )
                    );
        }else{
            $this->setOption(CURLOPT_POSTFIELDS, $this->cleanPost($data));
        }
    }
}

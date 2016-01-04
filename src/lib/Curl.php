<?php
/**
 * Curl class wrapper for http request / api call
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */

namespace mithun\http\lib;

use mithun\http\exception\Httpexception;
use mithun\http\Http;

/**
 * Curl Wrapper for Rest API's
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class Curl {
    /**
     * Constant Define for GET Method
     */
    const GET = 0;
    /**
     * Constant Define for POST Method
     */
    const POST = 1;
    /**
     * Constant Define for PUT Method
     */
    const PUT = 2;
    /**
     * Constant Define for DELETE Method
     */
    const DELETE = 3;
    /**
     * Constant Define for HEAD Method
     */
    const HEAD = 4;
    /**
     * Constant Define for OPTION Method
     */
    const OPTION = 5;

    /**
     * Url for Curl request
     * @var String 
     */
    protected $url;
    
    /**
     * Curl Resource
     * @var Resource
     */
    public $ch;

    /**
     * Curl Options
     * @var Array 
     */
    public $options = array();
    
    /**
     * Curl Information
     * @var mixed
     */
    public $info = array();
    
    /**
     * Curl Error Code if Any
     * @var Integer 
     */
    public $error_code = 0;
    
    /**
     * Error Message
     * @var String 
     */
    public $error_string = '';
    
    /**
     * Cookie to be passed on Curl
     * @var String 
     */
    public $cookie = '';

    /**
     * Some Valid Options for Curl
     * @var Array 
     */
    protected $validOptions = array(
                                'timeout'=>array('type'=>'integer'),
                                'login'=>array('type'=>'array'),
                                'proxy'=>array('type'=>'array'),
                                'proxylogin'=>array('type'=>'array'),
                                'setOptions'=>array('type'=>'array'),
                               );
        
    /**
     * For multi-curl callback function to be define here
     * @var array 
     */
    private $callable = array();
    
    
    /**
     * Object Constructor
     * @throws Httpexception when Curl module not found.
     */
    public function __construct(){
        if( !function_exists('curl_init') )
            throw new Httpexception('You must have CURL enabled in order to use this extension.');
        $this->ch = curl_init();
    }

    /**
     * Set option for HTTP Curl call
     * @param Integer $key Curl constant
     * @param Mixed $value values
     */
    public function setOption($key,$value){
        curl_setopt($this->ch,$key, $value);
    }
	
        
    /**
     * Formats Url if http:// dont exist
     * set http://
     * @param string $url
     */
    public function setUrl($url){
        if(!preg_match('!^\w+://! i', $url)) {
                $url = 'http://'.$url;
        }
        $this->url = $url;
    }



    /**
     * Set Cookie Value
     * @param Array $values
     * @throws Httpexception
     */
    public function setCookies($values){
        if (!is_array($values)){
            throw new Httpexception('options must be an array');
        }else{
            $params = $this->cleanPost($values);
        }
        $this->setOption(CURLOPT_COOKIE, $params);
    }
    
    /**
     * Valid Option Checker
     * @param Array $value
     * @param Array $validOptions
     * @throws Httpexception
     */
    protected static function checkOptions($value, $validOptions)
    {
        if (!empty($validOptions)) {
            foreach ($value as $key=>$val) {		
                if (!array_key_exists($key, $validOptions)) {
                    throw new Httpexception(printf('%s is not a valid option', $key));
                }
                $type = gettype($val);
                
                if ((!is_array($validOptions[$key]['type']) && ($type != $validOptions[$key]['type'])) || (is_array($validOptions[$key]['type']) && !in_array($type, $validOptions[$key]['type']))) {
                    throw new Httpexception(printf('%s must be of type $s',$key,$validOptions[$key]['type']));
                }

                if (($type == 'array') && array_key_exists('elements', $validOptions[$key])) {
                    self::checkOptions($val, $validOptions[$key]['elements']);
                }
            }
        }
    }

    /**
     * Check SSL url please modify if you has to do for https url.
     * @param string $url
     */
    protected function checksslurl($url){
        if((parse_url($url,PHP_URL_SCHEME) == 'https')||(parse_url($url,PHP_URL_PORT) == 443) ){
            $this->setOption(CURLOPT_SSLVERSION,3);
            $this->setOption(CURLOPT_SSL_VERIFYPEER, FALSE);
            $this->setOption(CURLOPT_SSL_VERIFYHOST, 2); 
        }
    }
    
    
    /**
     * Default options for Curl request.
     * 
     */
    protected function defaults(){
        
        isset($this->options['setOptions'][CURLOPT_HEADER]) ? $this->setOption(CURLOPT_HEADER,$this->options['setOptions'][CURLOPT_HEADER]) : $this->setOption(CURLOPT_HEADER,FALSE);
        
        isset($this->options['setOptions'][CURLOPT_RETURNTRANSFER]) ? $this->setOption(CURLOPT_RETURNTRANSFER,$this->options['setOptions'][CURLOPT_RETURNTRANSFER]) : $this->setOption(CURLOPT_RETURNTRANSFER,TRUE);

        isset($this->options['setOptions'][CURLOPT_FAILONERROR]) ? $this->setOption(CURLOPT_FAILONERROR,$this->options['setOptions'][CURLOPT_FAILONERROR]) : $this->setOption(CURLOPT_FAILONERROR,TRUE);  
        $this->setOption(CURLOPT_FOLLOWLOCATION, true);
    }
    
    /**
     * Alias for curl request processing default GET
     * @param String $url Target Url
     * @param Array $data Post values
     * @param boolean $jsonpost Whether JSON post or not
     * @param boolean $prepare whether prepare or not
     * @return mixed 
     * @throws Httpexception
     */
    public function sendRequest($url,$data = array(),$jsonpost = false, $prepare = false){
    	return $this->run($url,self::GET,$data,$jsonpost,$prepare);
    }

    /**
     * Main Function for Processing Curl
     * @param String $url Target Url
     * @param integer $method Curl Method
     * @param Array $data Post values
     * @param boolean $jsonpost Whether JSON post or not
     * @param boolean $prepare whether prepare or not
     * @return mixed 
     * @throws Httpexception
     */
    public function run($url,$method = self::GET,$data = array(),$jsonpost = false, $prepare = false){
        $this->setUrl($url);
        if( !$this->url ){
            throw new Httpexception('You must set Url.');
        }

        self::checkOptions($this->options,$this->validOptions);

        $this->checksslurl($url);
        
        $this->setOption(CURLOPT_URL,$this->url);
        
        $this->buildRequest($method, $data, $jsonpost);        

        if(isset($this->options['setOptions'])){
            foreach($this->options['setOptions'] as $k=>$v){
                $this->setOption($k,$v);
            }
        }
        
        if(!$prepare){
        	return $this->execute();
        }else{
        	return $this;
        }
    }


    /**
     * Execute Curl
     * @return type
     * @throws Httpexception
     */
    public function execute(){
    	$return = curl_exec($this->ch);
        try {
            if($return === false){
                throw new Httpexception(curl_error($this->ch),curl_errno($this->ch));
            }
            $this->info = curl_getinfo($this->ch);

            if(!Http::checkSuccess($this->info['http_code'])){
                throw new Httpexception("API call is not successful.",$this->info['http_code']);
            }
            curl_close($this->ch);
            return $return;

        } catch (Httpexception $ex) {
            curl_close($this->ch);
        }
    }




    /**
     * Arrays are walked through using the key as a the name.  Arrays
     * of Arrays are emitted as repeated fields consistent with such things
     * as checkboxes.
     * @desc Return data as a post string.
     * @param mixed by reference data to be written.
     * @param string [optional] name of the datum.
     */
    protected function &cleanPost(&$string, $name = NULL)
    {
        $thePostString = '' ;
        $thePrefix = $name ;

        if (is_array($string)){
            foreach($string as $k => $v){
                if ($thePrefix === NULL){
                        $thePostString .= '&' . self::cleanPost($v, $k) ;
                }else{
                        $thePostString .= '&' . self::cleanPost($v, $thePrefix . '[' . $k . ']') ;
                }
            }
        }else{
            $thePostString .= '&' . urlencode((string)$thePrefix) . '=' . urlencode($string) ;
        }		
        $r = substr($thePostString, 1) ;		
        return $r ;
    }

    /**
     * Authentication Using Curl
     * @param String $username
     * @param String $password
     */
    public function setHttpLogin($username = '', $password = '') {
        $this->setOption(CURLOPT_USERPWD, $username.':'.$password);
    }
	
	
    /**
     * Proxy Setting
     * sets proxy settings withouth username
     * @param String $url
     * @param Integer $port
     */
    public function setProxy($url,$port = 80){
        $this->setOption(CURLOPT_HTTPPROXYTUNNEL, TRUE);
        $this->setOption(CURLOPT_PROXY, $url.':'.$port);
    }

    /**
     * Proxy Login Setting
     * sets proxy login settings calls onley if is proxy setted
     * @param String $username
     * @param String $password
     */
    public function setProxyLogin($username = '', $password = '') {
        $this->setOption(CURLOPT_PROXYUSERPWD, $username.':'.$password);
    }
	
	
    /**
     * for multi-curl callback defination.
     * @param mixed $callable
     * @param Array $arg
     */
    public function multiCallback($callable,$arg = array()){
        $this->callable = array(
            'call' => $callable,
            'arg' => $arg
        );
    }
    
    /**
     * Callback execution after multi curl.
     * @param String $result
     */
    protected function exeCallback($result){
        try {
        	if(isset($this->callable['call'])){
            	call_user_func_array($this->callable['call'],  array_merge($this->callable['arg'],array($result)));
        	}
        } catch (Httpexception $ex) {
        	throw new Httpexception("Callback failed to execute.",500);
        }
    }
    
    /**
     * Build Request for Proper Curl request
     * @param Integer $method Method
     * @param Array $data Data 
     * @param boolean $json is JSON request
     */
    protected function buildRequest($method, $data,$json){        
        if($method == self::GET || $method == self::HEAD || $method == self::OPTION){            
            $this->defaults();
            if($method != self::GET){
                $this->setOption(CURLOPT_NOBODY, true);
                $this->setOption(CURLOPT_HEADER, true);
            }
        }else{
            $this->defaults();
            if($method == self::POST){
                    $this->setOption(CURLOPT_CUSTOMREQUEST, 'POST');
            }elseif($method == self::DELETE){
                $this->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
            }else{
                $this->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
            }
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
}

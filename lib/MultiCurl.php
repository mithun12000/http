<?php
/**
 * Multiple http request in paraller call wrapper
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
namespace mithun\http\lib;

/**
 * Multi Curl Wrapper for Rest API's
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class MultiCurl extends Curl {
    /**
     * Multi Curl Resource
     * @var Resource 
     */
    private $multi;

    /**
     * Adding Curl Object on Multi-curl process
     * @param \model\jobs\Curl $ch Curl instanse
     * @param String $name  Multi-curl object reference name
     */
    public function addChannel(Curl $ch,$name){
        $this->multi[$name] = $ch;
        curl_multi_add_handle($this->ch, $ch->ch);
    }

    /**
     * Main Function for Multi Curl process
     * @return Array
     */
    public function multiexecute(){
        $running = null;
        do {
                curl_multi_exec($this->ch, $running);
        } while($running > 0);


        // get content and remove handles
        foreach($this->multi as $id => $c) {
            $result[$id] = curl_multi_getcontent($c->ch);
            $c->exeCallback($result[$id]);
            curl_multi_remove_handle($mh, $c);
        }		
        // all done
        curl_multi_close($mh);
        return;		
    }
    
    
}

<?php 

class MageShop_Yapay_Service_Rest{
    /**
     * Inicio do request
     *
     * @var resource|false|CurlHandle
     */
    private $_curl;
    /**
     * Url para consultar
     *
     * @var string
     */
    private $_url;
    /**
     * Push
     *
     * @var string|array
     */
    private $data = null;
    /**
     * Cabeçalho
     *
     * @var array
     */
    private $headers = array('Content-Type: application/json');
    /**
     * Tipo da requisição
     *
     * @var string
     */
    private $method_header = "GET";
    private $options;
    private $_res;
    /**
     * Instancia
     *
     * @param string $url
     * @return object|MageShop_Yapay_Service_Rest
     */
    public function __construct()
    {
        $this->_curl = curl_init();
        return $this;
    }
    /**
     * Set url
     *
     * @param string $url
     * @return object
     */
    public function url($url)
    {
        $this->_url = $url;
        return $this;
    }
    /**
     * Define o cobeçalho da req
     *
     * @param array $header
     * @return object|MageShop_Yapay_Service_Rest
     */
    public function _header($header)
    {
        $this->headers = $header;
        return $this;
    }
    /**
     * Copo da req
     *
     * @param string|array $data
     * @return object|MageShop_Yapay_Service_Rest 
     */
    public function _body($data)
    {
        $this->data = $data;
        return $this;
    }
    /**
     * Tipo de req
     * 
     * @param string $method
     * @return object|MageShop_Yapay_Service_Rest
     */
    public function _method($method)
    {
        $this->method_header = $method;
        switch($method){
            case 'post':
                case 'POST':
                    $this->options[CURLOPT_POST] = true;
                    break;
        }
        return $this;
    }
    /**
     * Executa o curl
     *
     * @return object|MageShop_Yapay_Service_Rest
     */
    private function _inicialize()
    {
        $this->options[CURLOPT_URL] = $this->_url;
        $this->options[CURLOPT_CUSTOMREQUEST] = $this->method_header;
        $this->options[CURLOPT_HTTPHEADER] = $this->headers;
        $this->options[CURLOPT_RETURNTRANSFER] = true;
        $this->options[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;
        if($this->data !== null): $this->options[CURLOPT_POSTFIELDS] = $this->data; endif;
        return $this;
    }

    public function setCurl($field, $value)
    {
        $this->options[$field] = $value;
        return $this;
    }
    /**
     * Returno da api
     *
     * @return string|array
     */
    public function getResponse()
    {
        return isset($this->_res['response']) ? $this->_res['response'] : null ;
    }
    /**
     * Status de retorno
     *
     * @return int|string
     */
    public function getStatusCode()
    {
        return isset($this->_res['status_code']) ? $this->_res['status_code'] : null ;
    }
    /**
     * Executa uma requisição na api
     *
     * @return object|MageShop_Yapay_Service_Rest
     */
    public function exec()
    {
        $this->_inicialize();
        curl_setopt_array($this->_curl, $this->options);
        $response = curl_exec($this->_curl);
        if(curl_errno($this->_curl)) {
            throw new Exception("MageShop_Yapay_Service_Rest error: " . curl_error($this->_curl));
        }
        $status_code = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        curl_close($this->_curl);
        $this->_res = array(
            'status_code' => $status_code,
            'response' => $response
        );
        return $this;
    }
    /**
     * Armazena em log as requisições
     *
     * @param string $title
     * @param string $body
     * @param string $file
     * @return void
     */
    public function setLogYapay($title, $body, $file){
        $active = Mage::helper("mageshop_yapay/data")->getActiveLogs();
        if(in_array($active,[2,3])){
            Mage::log("[-- {$title} --] {$body}", null , $file, true);
        }
    }

}
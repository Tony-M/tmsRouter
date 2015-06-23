<?php

/**
 * Маршрутизатор
 * Class tmsRouter
 */
class tmsRouter
{
    /**
     * Путь к файлу кэша маршрутов
     * @var string | default: null
     */
    protected $CACHE_PATH = null;

    /**
     * Путь к файлу настроек маршрутов
     * @var string | default: null
     */
    protected $CONFIG_PATH = null;

    /**
     * Указывает на то, что файл кэша был создан только что и его не нужно обрабатывать
     * @var bool
     */
    protected $CACHE_JUST_CREATED = false;

    /**
     * Список известных маршрутов
     * @var array
     */
    protected $RULES = array();

    private static $_instance = null;

    /**
     * @param string $config_path absolute path to config file
     * @param string $cache_path absolute path to cache file
     * @throws Exception
     */
    public function __construct($config_path = null, $cache_path = null)
    {
//        echo __FILE__;
        if (!is_null($config_path)) {
            if (!file_exists($config_path) || !is_file($config_path)) {
                throw new Exception ('Routing config does not exist or undefined');
            }
            $this->CONFIG_PATH = $config_path;

        }


        if (!is_null($cache_path)) {
            if (!file_exists($cache_path) || !is_file($cache_path)) {
                if (!touch($cache_path)) {
                    throw new Exception ('Routing cache does not exist or undefined');
                }else{
                    chmod($cache_path , 0755);
                    $this->CACHE_JUST_CREATED = true;
                }
            }

            $this->CACHE_PATH = $cache_path;
        }

        $this->readRules();
    }

    public function __destruct(){
        if($this->CACHE_JUST_CREATED){
            $result = var_export($this->RULES, true);
            $result = preg_replace('/[\r\n\t]/','',$result);
            $result = preg_replace('/[ ]/','',$result);
            $result = '<?php'.PHP_EOL. '$rules='.$result.';'.PHP_EOL.'?>';
            file_put_contents($this->CACHE_PATH, $result);
        }
    }


    /**
     * Делает попытку чтения правил сначала из кэша а потом из настроек
     * @return bool
     */
    protected function readRules()
    {
        if (!$this->CACHE_JUST_CREATED && !is_null($this->CACHE_PATH)) {
            require_once $this->CACHE_PATH;

            if (isset($rules) && is_array($rules)) {
                $this->RULES = $rules;
            }
            return true;
        }

        if (!is_null($this->CONFIG_PATH)) {
            $rules = Spyc::YAMLLoad($this->CONFIG_PATH);
            if (is_array($rules)) {
                $this->RULES = $rules;
                return true;
            }
        }

        return false;
    }

    protected function __clone()
    {
    }

    private function __wakeup()
    {
    }

    static public function getInstance($config_path = null, $cache_path = null)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($config_path, $cache_path);
        }
        return self::$_instance;
    }

    /**
     * Возвращает все существующие маршруты
     * @return array
     */
    public function getRules()
    {
        return $this->RULES;
    }


    /**
     * Пытается вернуть параметры обработчика маршрута
     * @return Array|null
     */
    public function findRoute()
    {
        $path = $_SERVER['PHP_SELF'];
        $path = '/desire/?s=2/';
        $path = preg_replace('/(\?.*)$/','',$path);
        $path = preg_replace('/(\/)$/','',$path);

        foreach($this->RULES as $rule){
            if(!isset($rule['url']) || !isset($rule['params']))continue;

            if($rule['url']==$path){
                return $rule['params'];
            }
        }
        return null;
    }

    /**
     * Пытается вернуть параметры обработчика маршрута по умолчанию
     * @return Array|null
     */
    public  function findDefaultRoute(){
        if(isset($this->RULES['default']) && isset($this->RULES['default']['params'])){
            return $this->RULES['default']['params'];
        }
        return null;
    }

    /**
     * Возвращает путь для указанного именованного маршрута
     * @param string $name
     * @return string
     * @throws Exception
     */
    public function getRoute($name=null){
        if(is_null($name) || !isset($this->RULES[$name])|| !isset($this->RULES[$name]['url'])){
            throw new Exception ('Route not exists');
        }
        return $this->RULES[$name]['url'];
    }

}

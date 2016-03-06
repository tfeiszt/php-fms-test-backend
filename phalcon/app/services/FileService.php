<?php
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;

Class FileService
{

    /**
     * @var
     */
    public static $instance;

    protected $configuration;

    /**
     *
     */
    function __construct()
    {
        $di = \Phalcon\DI\FactoryDefault::getDefault();
        $config = $di->get('siteConfig');
        $this->configuration = $config->fileService;
    }


    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

}


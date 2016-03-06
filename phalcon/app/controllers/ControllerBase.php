<?php

use Phalcon\Mvc\Controller;
use Phalcon\Tag;
use Phalcon\DI\FactoryDefault;
use Phalcon\Config;

class ControllerBase extends Controller
{

    public function initialize()
    {
        $di = \Phalcon\DI\FactoryDefault::getDefault();
        $configFile = require APP_PATH . '/app/config/siteConfig.php';
        $config = new Config($configFile);
        $di->set('siteConfig', $config);

        Tag::setTitle($di->get('siteConfig')->site->defaultTitle);
        $this->assets
            ->collection('header')
            ->addCss('css/bootstrap.min.css')
            ->addCss('css/custom.css');
        $this->assets
            ->addJs('js/bootstrap.min.js')
            ->addJs('js/global.min.js');
    }


    public function fileService()
    {
        return FileService::getInstance();
    }

}

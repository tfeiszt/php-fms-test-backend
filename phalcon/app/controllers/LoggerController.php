<?php

use Phalcon\Tag;
use Phalcon\Http\Response;
use Phalcon\Http\Request;

class LoggerController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        $this->view->setLayout('base');
        Tag::prependTitle('Log');
    }

    public function indexAction()
    {
        $model = new Logs();
        $this->view->logs = $model::find(array('order' => 'created_at DESC'));
    }

}
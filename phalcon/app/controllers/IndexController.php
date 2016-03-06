<?php

use Phalcon\Tag;

class IndexController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        $this->view->setLayout('base');
        Tag::prependTitle('Welcome');
    }

    public function indexAction()
    {

    }

}


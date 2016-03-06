<?php

use Phalcon\Tag;

class ManagerController extends ControllerBase
{


    public function initialize()
    {
        parent::initialize();
        $this->view->setLayout('base');
        Tag::prependTitle('File Manager');
    }

    public function indexAction()
    {
        $this->view->service = $this->fileService();
    }

}


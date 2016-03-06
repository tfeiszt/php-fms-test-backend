<?php

use Phalcon\Tag;
use Phalcon\Http\Response;
use Phalcon\Http\Request;

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


    public function createFileAction()
    {
        $this->view->disable();
        $response = new Response();

        $request = new Request();

        if ($request->isPost()) {

            if ($request->isAjax()) {
                $result = $this->fileService()->createFile($request);
            }
        }

        //Set the content of the response
        $response->setContent(json_encode(isset($result) ? $result : ['success' => false]));

        //Return the response
        return $response;
    }


    public function createFolderAction()
    {
        $this->view->disable();
        $response = new Response();

        $request = new Request();

        if ($request->isPost()) {

            if ($request->isAjax()) {
                $result = $this->fileService()->createFolder($request);
            }
        }

        //Set the content of the response
        $response->setContent(json_encode(isset($result) ? $result : ['success' => false]));

        //Return the response
        return $response;
    }


    public function scanFolderAction()
    {
        $this->view->disable();
        $response = new Response();

        $request = new Request();

        if ($request->isPost()) {

            if ($request->isAjax()) {
                $result = $this->fileService()->listFolder($request);
                foreach($result['data'] as $k => $item){
                   $result['data'][$k] = $this->view->getPartial('manager/item', array('item' => $item));
                }
            }
        }

        //Set the content of the response
        $response->setContent(json_encode(isset($result) ? $result : ['success' => false]));

        //Return the response
        return $response;
    }
}


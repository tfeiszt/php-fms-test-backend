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

        $response->setContent(json_encode(isset($result) ? $result : ['success' => false]));

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
                foreach($result['data']['entities'] as $k => $item){
                    if (get_class($item) == 'File') {
                        $result['data']['entities'][$k] = $this->view->getPartial('manager/itemFile', array('item' => $item));
                    } else {
                        $result['data']['entities'][$k] = $this->view->getPartial('manager/itemFolder', array('item' => $item));
                    }

                }
            }
        }

        $response->setContent(json_encode(isset($result) ? $result : ['success' => false]));

        return $response;
    }


    public function copyAction()
    {
        $this->view->disable();
        $response = new Response();

        $request = new Request();

        if ($request->isPost()) {

            if ($request->isAjax()) {
                $result = $this->fileService()->copy($request);
            }
        }

        $response->setContent(json_encode(isset($result) ? $result : ['success' => false]));

        return $response;
    }
}


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
                if ($result['success']) {
                    $this->log(['method' => 'create', 'type' => get_class($result['data']), 'objname' => $result['data']->getName() ]);
                }
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
                if ($result['success']) {
                    $this->log(['method' => 'create', 'type' => get_class($result['data']), 'objname' => $result['data']->getName() ]);
                }
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
                        if ($item->getName() == '..') {
                            $details = '';
                        } else {
                            $count = $this->fileService()->getFileCount($item);
                            $count .= ((int)$count == 1) ? ' file' : ' files';
                            $size = $this->fileService()->getFileSizes($item). 'Kb';
                            $details = '[' . $count . ',' . $size . ']';
                        }
                        $result['data']['entities'][$k] = $this->view->getPartial('manager/itemFolder', array('item' => $item, 'details' => $details));
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
                if ($result['success']) {
                    $this->log(['method' => 'create', 'type' => get_class($result['data']), 'objname' => $result['data']->getName() ]);
                }
            }
        }

        $response->setContent(json_encode(isset($result) ? $result : ['success' => false]));

        return $response;
    }


    public function moveAction()
    {
        $this->view->disable();
        $response = new Response();

        $request = new Request();

        if ($request->isPost()) {

            if ($request->isAjax()) {
                $result = $this->fileService()->move($request);
                if ($result['success']) {
                    $this->log(['method' => 'move', 'type' => get_class($result['data']), 'objname' => $result['data']->getName() ]);
                }
            }
        }

        $response->setContent(json_encode(isset($result) ? $result : ['success' => false]));

        return $response;
    }


    public function renameObjectAction()
    {
        $this->view->disable();
        $response = new Response();

        $request = new Request();

        if ($request->isPost()) {

            if ($request->isAjax()) {
                $result = $this->fileService()->rename($request);
                if ($result['success']) {
                    $this->log(['method' => 'rename', 'type' => get_class($result['data']), 'objname' => $result['data']->getName() ]);
                }
            }
        }

        $response->setContent(json_encode(isset($result) ? $result : ['success' => false]));

        return $response;
    }


    public function deleteObjectAction()
    {
        $this->view->disable();
        $response = new Response();

        $request = new Request();

        if ($request->isPost()) {

            if ($request->isAjax()) {
                $name  = $request->getPost('name');
                $parent = $request->getPost('parent');
                $obj = $this->fileService()->getFileOrFolder($this->fileService()->getPathAndName($parent, $name));
                $result = $this->fileService()->delete($request);
                if ($result['success']) {
                    $this->log(['method' => 'delete', 'type' => get_class($obj), 'objname' => $obj->getName() ]);
                }
            }
        }

        $response->setContent(json_encode(isset($result) ? $result : ['success' => false]));

        return $response;
    }

    protected function log($data)
    {
        $model = new Logs();

        $model->method = $data['method'];
        $model->type = $data['type'];
        $model->objname = $data['objname'];
        $model->created_at = Date('Y-m-d H:i');
        return $model->save();
    }
}


<?php
use Phalcon\Config;
use Phalcon\FileInterface;
use Phalcon\FolderInterface;
use Phalcon\FileSystemInterface;
use Phalcon\DI\FactoryDefault;
use Phalcon\Http\Request;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Filter;

Class FileService
{

    /**
     * @var
     */
    public static $instance;

    protected $configuration;

    protected $fileSystem;

    /**
     *
     */
    function __construct()
    {
        $di = \Phalcon\DI\FactoryDefault::getDefault();
        $config = $di->get('siteConfig');

        $this->configuration = $config->fileService;
        $rootFolder = new Folder($this->configuration->rootFolder);
        $this->fileSystem = new FileSystem($rootFolder);
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


    private function concateMessages($messages, $glue = '<br>')
    {
        $result = '';
        $sep = '';
        if (count($messages)) {
            foreach ($messages as $message) {
                $result .= $sep . $message;
                $sep = $glue;
            }
        }
        return $result;
    }


    public function createFile(Request $request)
    {
        $result = array('success' => true, 'data' => null, 'message' => '');
        $validation = new Validation();
        $validation->add(
            'name',
            new PresenceOf(
                array(
                    'message' => 'The name is required'
                )
            )
        );
        $messages = $validation->validate($_POST);
        if (count($messages) === 0) {
            $folder = $this->getFolderOrCreateIfNecessary(($request->getPost('parent') != '') ? $request->getPost('parent') : $this->configuration->rootFolder );// new Folder(($request->getPost('parent') != '') ? $request->getPost('parent') : $this->configuration->rootFolder );
            $file = new File();
            $file->setName($request->getPost('name') . '.txt')
                ->setParentFolder($folder)
                ->setContent($request->getPost('content'));
            $result['data'] = $this->fileSystem->createFile($file, $folder);
        } else {
            $result['success'] = false;
            $result['message'] = $this->concateMessages($messages);
        }
        return $result;
    }


    public function createFolder(Request $request)
    {
        $result = array('success' => true, 'data' => null, 'message' => '');
        $validation = new Validation();
        $validation->add(
            'name',
            new PresenceOf(
                array(
                    'message' => 'The name is required'
                )
            )
        );
        $messages = $validation->validate($_POST);
        if (count($messages) === 0) {
            $parent = new Folder(($request->getPost('parent') != '') ? $request->getPost('parent') : $this->configuration->rootFolder);
            $folder = new Folder($this->fileSystem->setPathSlash($parent->getPath()) . $request->getPost('name'));

            if (!$this->isFolderExists($folder->getPath())) {
                $result['data'] = $this->fileSystem->createFolder($folder, $parent);
            } else {
                $result['success'] = false;
                $result['message'] = 'Directory does exist';
            }
        } else {
            $result['success'] = false;
            $result['message'] = $this->concateMessages($messages);
        }
        return $result;
    }


    public function listFolder(Request $request)
    {
        $result = array('success' => true, 'data' => ['entry_point' => '', 'entities' => []], 'message' => '');

        $folder = new Folder(($request->getPost('folder') != '') ? $request->getPost('folder') : $this->configuration->rootFolder);

        if ($this->isFolderExists($folder->getPath())) {
            $result['data']['entry_point'] = str_replace(dirname($this->configuration->rootFolder), '', $folder->getPath());
            $result['data']['entry_path'] =  $folder->getPath();
            $result['data']['entities'] = $this->fileSystem->getFiles($folder);
        } else {
            $result['success'] = false;
            $result['message'] = 'Directory does not exist';
        }
        return $result;
    }


    public function copy(Request $request)
    {
        $result = array('success' => true, 'data' => ['entry_point' => '', 'entities' => []], 'message' => '');

        $validation = new Validation();
        $validation->add(
            'name',
            new PresenceOf(
                array(
                    'message' => 'The name is required'
                )
            )
        );
        $validation->add(
            'target',
            new PresenceOf(
                array(
                    'message' => 'The target folder is required'
                )
            )
        );
        $validation->add(
            'source',
            new PresenceOf(
                array(
                    'message' => 'The source folder is required'
                )
            )
        );
        $messages = $validation->validate($_POST);
        if (count($messages) === 0) {
            $entry = $request->getPost('name');
            $target = $request->getPost('target');
            $source = $request->getPost('source');

            if (file_exists($this->fileSystem->setPathSlash($source) . $entry) && (file_exists($target) && is_directory($target))){
                if (is_directory($entry)) {
                    $soruceFolder = new Folder($this->fileSystem->setPathSlash($source). $entry);
                } else {
                    $sourceFile =new File($this->fileSystem->setPathSlash($source) . $entry);

                    $targetFolder =  new Folder($target);

                    $file = new File();
                    $file->setName($request->getPost('name'))
                        ->setParentFolder($targetFolder)
                        ->setContent($sourceFile->getContent());
                    $result['data'] = $this->fileSystem->createFile($file, $targetFolder);
                }

            } else {
                $result['success'] = false;
                $result['message'] = 'Invalid directory or filename';
            }

        } else {
            $result['success'] = false;
            $result['message'] = $this->concateMessages($messages);
        }
        return $result;
    }


    public function getFolderOrCreateIfNecessary($pathAndName)
    {
        if (! $this->isFolderExists($pathAndName)) {
            $this->fileSystem->createFolder(new Folder($pathAndName), new Folder(dirname($pathAndName)));
        }
        return new Folder($pathAndName);
    }


    public function isFolderExists($pathAndName)
    {
        return (file_exists($pathAndName)) ? true :false;
    }


    public function isFileExists($pathAndName)
    {
        return (is_file($pathAndName)) ? true :false;
    }


    public function getPathInfo($pathAndName)
    {
        $result = new stdClass();
        $result->dirName = pathinfo($pathAndName, PATHINFO_DIRNAME);
        $result->baseName = pathinfo($pathAndName, PATHINFO_BASENAME);
        $result->fileExtension = pathinfo($pathAndName, PATHINFO_EXTENSION);
        $result->fileName = pathinfo($pathAndName, PATHINFO_FILENAME);
        return $result;
    }

}


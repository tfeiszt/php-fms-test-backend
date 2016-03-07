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
            $result['data']['entities'] = $this->getFilesAndFolders($folder);
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

            if (file_exists($this->fileSystem->setPathSlash($source) . $entry) && (file_exists($target) && is_dir($target))){

                $targetFolder =  new Folder($target);

                if (is_dir($this->fileSystem->setPathSlash($source) . $entry)) {
                    $sourceFolder = new Folder($this->fileSystem->setPathSlash($source). $entry);
                    $result['data'] = $this->copyRecursive([$sourceFolder], $targetFolder);
                } else {
                    $sourceFile =new File($this->fileSystem->setPathSlash($source) . $entry);
                    $result['data'] = $this->copyRecursive([$sourceFile], $targetFolder);
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


    public function move(Request $request)
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

            if (file_exists($this->fileSystem->setPathSlash($source) . $entry) && (file_exists($target) && is_dir($target))){

                $targetFolder =  new Folder($target);

                if (is_dir($this->fileSystem->setPathSlash($source) . $entry)) {
                    $sourceFolder = new Folder($this->fileSystem->setPathSlash($source). $entry);
                    $result['data'] = $this->fileSystem->renameFolder($sourceFolder, $this->fileSystem->setPathSlash(realpath($targetFolder->getPath())) . $entry);
                } else {
                    $sourceFile =new File($this->fileSystem->setPathSlash($source) . $entry);
                    $result['data'] = $this->fileSystem->renameFile($sourceFile, $this->fileSystem->setPathSlash(realpath($targetFolder->getPath())) . $entry);
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


    private function copyRecursive($sourceFiles, Folder $targetFolder)
    {
        foreach($sourceFiles as $fileOrFolder) {
            if (get_class($fileOrFolder) == 'Folder') {
                $newTarget = $this->getFolderOrCreateIfNecessary($this->fileSystem->setPathSlash($targetFolder->getPath()) . $fileOrFolder->getName());
                $files = $this->getFilesAndFolders($fileOrFolder);
                if (count($files) > 0) {
                    foreach($files as $k => $file) {
                        if ($file->getName() == '..') {
                            unset($files[$k]);
                        }
                    }
                    $result = $this->copyRecursive($files, $newTarget);
                } else {
                    $result = $newTarget;
                }
            } else {
                $file = new File();
                $file->setName($fileOrFolder->getName())
                    ->setParentFolder($fileOrFolder->getParentFolder())
                    ->setContent($fileOrFolder->getContent());
                $result =  $this->fileSystem->createFile($file, $targetFolder);
            }
        }
        return $result;
    }


    public function rename(Request $request)
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
            'old_name',
            new PresenceOf(
                array(
                    'message' => 'The source object is required'
                )
            )
        );
        $validation->add(
            'parent',
            new PresenceOf(
                array(
                    'message' => 'Location is required'
                )
            )
        );
        $messages = $validation->validate($_POST);
        if (count($messages) === 0) {
            $target = $request->getPost('name');
            $source = $request->getPost('old_name');
            $parent = $request->getPost('parent');

            if (file_exists($this->fileSystem->setPathSlash($parent) . $source) && (! file_exists($this->fileSystem->setPathSlash($parent) . $target))){

                if (is_dir($this->fileSystem->setPathSlash($parent) . $source)) {
                    $sourceFolder = new Folder($this->fileSystem->setPathSlash($parent) . $source);
                    $result['data'] = $this->fileSystem->renameFolder($sourceFolder, $this->fileSystem->setPathSlash(realpath(pathinfo($sourceFolder->getPath(), PATHINFO_DIRNAME))) . $target);
                } else {
                    $sourceFile =new File($this->fileSystem->setPathSlash($parent) . $source);
                    $result['data'] = $this->fileSystem->renameFile($sourceFile, $this->fileSystem->setPathSlash(realpath($sourceFile->getPath())) . $target);
                }

            } else {
                $result['success'] = false;
                $result['message'] = 'The file already exists';
            }

        } else {
            $result['success'] = false;
            $result['message'] = $this->concateMessages($messages);
        }
        return $result;
    }


    public function delete(Request $request)
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
            'parent',
            new PresenceOf(
                array(
                    'message' => 'Location is required'
                )
            )
        );
        $messages = $validation->validate($_POST);
        if (count($messages) === 0) {
            $source = $request->getPost('name');
            $parent = $request->getPost('parent');

            if (file_exists($this->fileSystem->setPathSlash($parent) . $source)){

                if (is_dir($this->fileSystem->setPathSlash($parent) . $source)) {
                    $sourceFolder = new Folder($this->fileSystem->setPathSlash($parent) . $source);
                    $result['data'] = $this->fileSystem->deleteFolder($sourceFolder);
                } else {
                    $sourceFile =new File($this->fileSystem->setPathSlash($parent) . $source);
                    $result['data'] = $this->fileSystem->deleteFile($sourceFile);
                }

            } else {
                $result['success'] = false;
                $result['message'] = 'The file does not exist';
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


    private function getFilesAndFolders(Folder $folder)
    {
        $result = [];
        if (realpath($folder->getPath()) != realpath($this->fileSystem->getRootFolder()->getPath())) {
            $parent =  new Folder(dirname($folder->getPath()));
            $result[] = $parent->setName('..');
        }

        $result = array_merge($result, $this->fileSystem->getFolders($folder));
        $result = array_merge($result, $this->fileSystem->getFiles($folder));
        return $result;
    }


    public function getFileCount(Folder $folder)
    {
        return $this->fileSystem->getFileCount($folder);
    }


    public function getFileSizes(Folder $folder)
    {
        return $this->fileSystem->getDirectorySize($folder);
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


    public function getPathAndName($path, $name)
    {
        return $this->fileSystem->setPathSlash($path) . $name;
    }


    public function getFileOrFolder($pathAndName)
    {
        if (file_exists($pathAndName)) {
            if (is_dir($pathAndName)) {
                return new Folder($pathAndName);
            } else {
                return new File($pathAndName);
            }
        } else {
            return false;
        }
    }

}


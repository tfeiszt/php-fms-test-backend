<?php

Class FileSystem implements FileSystemInterface
{

    /**
     * @var
     */
    public static $instance;

    protected $rootFolder;


    public function __construct($rootFolder)
    {
        $this->rootFolder = $rootFolder;
        $this->createRootFolder($this->rootFolder);
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


    /**
     * @param $path
     * @return string
     */
    public function setPathSlash($path)
    {
        if (substr($path, -1) != '/') {
            $path = $path . '/';
        }
        return $path;
    }


    private function sortByName($items)
    {
        $sorted = array();
        foreach ($items as $key => $itemObject)
        {
            $sorted[$key] = $itemObject->getName();
        }
        array_multisort($sorted, SORT_ASC, $items);
        return $items;
    }


    /**
     * @param FileInterface   $file
     * @param FolderInterface $parent
     *
     * @return FileInterface
     */
    public function createFile(FileInterface $file, FolderInterface $parent)
    {
        $fh = fopen($this->setPathSlash($parent->getPath()) . $file->getName(), 'w');
        fwrite($fh, $file->getContent());
        fclose($fh);
        @chmod($parent->getPath() . $file->getName(), 0777);
        $file->setParentFolder($parent);
        return $file;
    }
    /**
     * @param FileInterface $file
     *
     * @return FileInterface
     */
    public function updateFile(FileInterface $file)
    {
        $fh = fopen($file->getPath(), 'w');
        fwrite($fh, $file->getContent());
        fclose($fh);
        @chmod($file->getPath(), 0777);
        return $file;
    }

    /**
     * @param FileInterface $file
     * @param               $newName
     *
     * @return FileInterface
     */
    public function renameFile(FileInterface $file, $newName)
    {
        return $file;
    }

    /**
     * @param FileInterface $file
     *
     * @return bool
     */
    public function deleteFile(FileInterface $file)
    {
        return $file;
    }

    /**
     * @param FolderInterface $folder
     *
     * @return FolderInterface
     */
    public function createRootFolder(FolderInterface $folder)
    {
        return $folder;
    }

    /**
     * @return mixed
     */
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * @param FolderInterface $folder
     * @param FolderInterface $parent
     *
     * @return FolderInterface
     */
    public function createFolder( FolderInterface $folder, FolderInterface $parent)
    {
        mkdir($this->setPathSlash($parent->getPath()) . $folder->getName(), 0775, true);
        return $folder;
    }


    /**
     * @param FolderInterface $folder
     *
     * @return bool
     */
    public function deleteFolder(FolderInterface $folder){
        return true;
    }

    /**
     * @param FolderInterface $folder
     * @param                 $newName
     *
     * @return FolderInterface
     */
    public function renameFolder(FolderInterface $folder, $newName){

        return $folder;
    }
    /**
     * @param FolderInterface $folder
     *
     * @return int
     */
    public function getFolderCount(FolderInterface $folder)
    {
        return 0;
    }

    /**
     * @param FolderInterface $folder
     *
     * @return int
     */
    public function getFileCount(FolderInterface $folder)
    {
        return 0;
    }
    /**
     * @param FolderInterface $folder
     *
     * @return int
     */
    public function getDirectorySize(FolderInterface $folder)
    {
        return 0;
    }

    /**
     * @param FolderInterface $folder
     *
     * @return FolderInterface[]
     */
    public function getFolders(FolderInterface $folder)
    {
        $result = [];
        if ($handle = opendir($folder->getPath())) {
            while (false !== ($entry = readdir($handle))) {
                if (is_directory($entry)) {
                    $result[] =  new Folder($entry);
                }
            }
            closedir($handle);
        }
        return [];
    }


    /**
     * @param FolderInterface $folder
     *
     * @return FileInterface[]
     */
    public function getFiles(FolderInterface $folder)
    {
        $result = [];
        $folders = [];
        $files = [];
        if ($handle = opendir($folder->getPath())) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != ".") {
                    if (is_dir($this->setPathSlash($folder->getPath()) . $entry)) {
                        $folders[] = new Folder($this->setPathSlash($folder->getPath()) . $entry); //TODO
                    } else {
                        $files[] = new File($this->setPathSlash($folder->getPath()) . $entry); //TODO
                    }
                }
            }
            closedir($handle);
        }

        $folders = $this->sortByName($folders);
        $files = $this->sortByName($files);
        $result = array_merge($result, $folders);
        $result = array_merge($result, $files);
        return $result;
    }
}
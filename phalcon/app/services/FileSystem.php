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
        return (file_exists($file->getPath())) ? false : true;
    }

    /**
     * @param FolderInterface $folder
     *
     * @return FolderInterface
     */
    public function createRootFolder(FolderInterface $folder)
    {
        if (! file_exists($this->setPathSlash($folder->getPath()))){
            mkdir($this->setPathSlash($folder->getPath()), 0775, true);
            $this->rootFolder = new Folder($this->setPathSlash($folder->getPath())); //Using this way, created time has been completed.
        }
        return $this->rootFolder;
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
        return new Folder($this->setPathSlash($parent->getPath()) . $folder->getName());
    }


    /**
     * @param FolderInterface $folder
     *
     * @return bool
     */
    public function deleteFolder(FolderInterface $folder){
        $files = array_diff(scandir($folder->getPath()), array('.','..'));
        foreach ($files as $fileOrFolder) {
            (is_dir($this->setPathSlash($folder->getPath()) . $fileOrFolder)) ? delTree($this->setPathSlash($folder->getPath()) . $fileOrFolder) : unlink($this->setPathSlash($folder->getPath()) . $fileOrFolder);
        }
        return rmdir($this->setPathSlash($folder->getPath()));
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
        $tmp = $this->getFolders($folder);
        $result = 0;
        foreach($tmp as $item) {
            if (get_class($item) == 'Folder') {
                $result += $this->getFileCount($item);
            } else {
                $result++;
            }
        }
        return $result;
    }

    /**
     * @param FolderInterface $folder
     *
     * @return int
     */
    public function getFileCount(FolderInterface $folder)
    {
        $tmp = $this->getFiles($folder);
        $result = count($tmp);
        $tmp = $this->getFolders($folder);
        foreach($tmp as $item) {
           $result += $this->getFileCount($item);
        }
        return $result;
    }
    /**
     * @param FolderInterface $folder
     *
     * @return int
     */
    public function getDirectorySize(FolderInterface $folder)
    {
        $result = 0;
        $tmp = $this->getFiles($folder);
        foreach($tmp as $item) {
            $result += $item->getSize();
        }
        $tmp = $this->getFolders($folder);
        foreach($tmp as $item) {
            $result += $this->getFileCount($item);
        }
        return $result;
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
                if (is_dir($this->setPathSlash($folder->getPath()) . $entry)) {
                    if (($entry != ".") && ($entry != "..")) {
                        $result[] = new Folder($this->setPathSlash($folder->getPath()) . $entry);
                    }
                }
            }
            closedir($handle);
        }
        $result = $this->sortByName($result);

        return $result;
    }


    /**
     * @param FolderInterface $folder
     *
     * @return FileInterface[]
     */
    public function getFiles(FolderInterface $folder)
    {
        $result = [];
        if ($handle = opendir($folder->getPath())) {
            while (false !== ($entry = readdir($handle))) {
                if (! is_dir($this->setPathSlash($folder->getPath()) . $entry)) {
                    $result[] = new File($this->setPathSlash($folder->getPath()) . $entry); //TODO
                }
            }
            closedir($handle);
        }

        $result = $this->sortByName($result);
        return $result;
    }
}
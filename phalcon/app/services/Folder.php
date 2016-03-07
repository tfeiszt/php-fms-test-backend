<?php
Class Folder implements FolderInterface
{
    /**
     * @var
     */
    protected $name;
    protected $path;
    protected $createdTime;


    public function __construct($pathAndName)
    {
        $this->setName(pathinfo($pathAndName, PATHINFO_BASENAME));
        $this->setPath(realpath($pathAndName));
        if (file_exists($pathAndName)){
            $this->setCreatedTime(filemtime($pathAndName));
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedTime()
    {
        return $this->createdTime;
    }

    /**
     * @param DateTime $created
     *
     * @return $this
     */
    public function setCreatedTime($created)
    {
        $this->createdTime = $created;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
}
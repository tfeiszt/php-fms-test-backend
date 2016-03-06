<?php

Class File implements FileInterface
{
    protected $name;
    protected $createdTime;
    protected $modifiedTime;
    protected $size;
    protected $parentFolder;
    protected $fileContent;

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
     * @return int
     */
    public function getSize()
    {
        return 0;
    }

    /**
     * @param int $size
     *
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
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
     * @return DateTime
     */
    public function getModifiedTime()
    {
        return $this->modifiedTime;
    }

    /**
     * @param DateTime $modified
     *
     * @return $this
     */
    public function setModifiedTime($modified)
    {
        $this->modifiedTime = $modified;
        return $this;
    }

    /**
     * @return FolderInterface
     */
    public function getParentFolder()
    {
        return $this->parentFolder;
    }

    /**
     * @param FolderInterface $parent
     *
     * @return $this
     */
    public function setParentFolder(FolderInterface $parent)
    {
        $this->parentFolder = $parent;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->parentFolder->getPath();
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->fileContent = $content;
        return $this;
    }


    public function getContent()
    {
        return $this->fileContent;
    }
}
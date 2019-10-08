<?php

/**
 * @site https://www.ipip.net
 * @desc Parse IP library in ipdb format
 * @copyright IPIP.net
 */

namespace ipip\db;

class IpDbReader extends Reader
{
    private $file;

    private $database;

    /**
     * Reader constructor.
     * @param $database
     * @throws \Exception
     */
    public function __construct($database)
    {
        $this->database = $database;

        if (is_readable($this->database) === false){
            throw new \InvalidArgumentException("The IP Database file \"{$this->database}\" does not exist or is not readable.");
        }

        $this->file = @fopen($this->database, 'rb');
        if ($this->file === false){
            throw new \InvalidArgumentException("IP Database File opening \"{$this->database}\".");
        }

        $this->init();
    }

    /**
     * @inheritDoc
     */
    protected function computeFileSize()
    {
        return @filesize($this->database);
    }

    /**
     * @inheritDoc
     */
    protected function read($offset, $length)
    {
        if (0 !== fseek($this->file, $offset)){
            return false;
        }

        return fread($this->file, $length);
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        if (is_resource($this->file)){
            fclose($this->file);
        }
    }
}

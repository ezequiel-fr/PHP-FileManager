<?php

namespace FileManager\Methods;

use FileManager\Except;

/**
 * Upload files from $_FILES request
 */

class Upload {

    /**
     * Directory separator
     * 
     * @var string DIR_SEPARATOR
     */

    private const DIR_SEPARATOR = '/';


    /**
     * The allowed extensions of files to upload.
     * 
     * @var array $_allowed_extensions
     */

    public readonly array $_allowed_extensions;


    /**
     * The allowed extensions of files to upload.
     * 
     * @var array $_allowed_extensions
     */

    public readonly array $_not_allowed_extensions;


    /**
     * The limit size that the files can have when uploading.
     * 
     * @var int $_limit_file_size The limit size
     */

    public readonly int $_limit_file_size;


    /**
     * The units used to get a file size.
     * 
     * @var array $units
     */

    protected array $units = array('B', 'kB', 'MB', 'GB', 'TB');


    /** 
     * @return void
     */

    public function __construct()
    {
        // Set the limit file's size
        $this->setLimitFileSize();
    }


    /**
     * Define allowed extensions.
     * 
     * Define allowed extensions to upload. You can also provide a string with one
     * value, or of course an array.\
     * You can also give a path to a JSON file to get more extenions and have a
     * more readable code.\
     * Only values with this extension can be upload, others will be rejected.\
     * By default, it'll use the `allowed_extensions.json` file in the JSON folder.
     * 
     * @param array|string $extensions The extensions or JSON file with extensions.
     * 
     * @return void
     */

    public function setAllowedExtensions(array|string $extensions = ""): void
    {
        if (empty($extensions))
            $extensions = '../../json/allowed_extensions.json';

        $this->_allowed_extensions = $this->setExtensions($extensions);
    }


    /**
     * Define forbidden extensions.
     * 
     * Define forbidden extensions to upload. You can also provide a string with one
     * value, or of course an array.\
     * You can also give a path to a JSON file to get more extenions and have a
     * more readable code.\
     * By default, it'll use the `forbidden_extensions.json` file in the JSON folder.
     * 
     * @param array|string $extensions The extensions or JSON file with extensions.
     * 
     * @return void
     */

    public function setForbiddenExtensions(array|string $extensions = ""): void
    {
        if (empty($extensions))
            $extensions = '../../json/forbidden_extensions.json';

        $this->_not_allowed_extensions = $this->setExtensions($extensions);
    }


    /**
     * setExtensions child
     * 
     * Return an array containing the extensions.
     *  - If an array, will flatten it before return it
     *  - If a single string, will return an array with this string
     *  - If string of a JSON file $path will return 
     * 
     * @param array|string $extensions The extensions or JSON file with extensions.
     * @param ?bool|int $is_file
     * 
     * @return ?array
     */

    private function setExtensions(array|string $extensions): ?array
    {
        // get real path
        $file = realpath(dirname(__FILE__).$this::DIR_SEPARATOR.$extensions);

        if (@$file && is_file($file)) {
            if (strtolower(substr(strrchr($file, '.'), 1)) === "json") {
                // If JSON cannot be read (ex: file not found, ...)
                // Throw new error
                if ($json = file_get_contents($file)) {
                    $result = json_decode($json, true);
                } else [new Except, "exception"]("Cannot read file.");
            } else [new Except, 'error']("File type must be a JSON file");
        } else if (is_string($extensions))
            // Convert string to array
            $result = array($extensions);
        
        // Flatten array
        $ext = [];

        array_walk_recursive(
            $result,
            function ($a) use (&$ext) {
                $ext[] = $a;
            }
        );

        return $ext;
    }


    /**
     * Set a limit of uploaded files size
     * 
     * Defines a limit to the size of the files that will be uploaded. \
     * The limit can be writed as a number but as well as string like
     * *2MB*, *400KB*, ... .\
     * The size must be within a range of 0 byte (0) and 1 TeraByte (1 TB).
     * 
     * @param int|string $limit
     * 
     * @return int The limit
     */

    public function setLimitFileSize(int|string $limit = "2MB")
    {
        if (is_int($limit)) {
            $this->_limit_file_size = $limit;
        } else if (is_string($limit)) {
            $units = array_reverse($this->units);
            $unitUsed = "";

            foreach ($units as $a) {
                if (stripos($limit, $a) && empty($unitUsed))
                    $unitUsed = $a;
            }

            $this->_limit_file_size = (
                intval(substr($limit, 0, strlen($limit) - strlen($unitUsed)))
                * 1024 **
                array_search($unitUsed, $this->units)
            );
        }

        return $this->_limit_file_size;
    }

}

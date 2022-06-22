<?php

namespace FileManager\Methods;

class Upload {

    private const DIR_SEPARATOR = '/';

    public $_allowed_extensions = [];
    public $_limit_file_size;
    public $_files;

    static protected $units = array('B', 'kB', 'MB', 'GB', 'TB');

    public function __construct($values, array|string $allowedExtenions = "") {
        var_dump($allowedExtenions);

        if ($allowedExtenions !== "")
            $this->setAllowedExtensions($allowedExtenions);
        
        $this->_files = $values;

        $this->setLimitFileSize();
    }


    protected function setAllowedExtensions(array|string $allowedExtenions)
    {
        if (is_string($allowedExtenions)) {
            $allowedExtenions = dirname(__DIR__).DIRECTORY_SEPARATOR.$allowedExtenions;

            if ($json = file_get_contents($allowedExtenions)) {
                $content = json_decode($json, true);

                $extensions = [];

                array_walk_recursive($content, function ($a) use (&$extensions) {
                    $extensions[] = $a;
                });
            }
        } else $extensions = $allowedExtenions;

        $this->_allowed_extensions = $extensions;
    }


    public function setLimitFileSize(int|string $limit = "2MB")
    {
        if (is_int($limit)) {
            $this->_limit_file_size = $limit;
        } else if (is_string($limit)) {
            $units = array_reverse($this::$units);
            $unitUsed = "";

            foreach ($units as $a) {
                if (stripos($limit, $a) && empty($unitUsed))
                    $unitUsed = $a;
            }

            $this->_limit_file_size = (
                intval(substr($limit, 0, strlen($limit) - strlen($unitUsed)))
                * 1024 **
                array_search($unitUsed, $this::$units)
            );
        }

        return $this->_limit_file_size;
    }


    protected function checkExtension(string $file, bool|int $returnExtension = false)
    {
        // To do : check if there is an ext. (ex: "./test/LISCENCE")
        $extUpload = strtolower(substr(strrchr($file, '.'), 1));

        if ($this->_allowed_extensions != []) {
            return in_array($extUpload, $this->_allowed_extensions)
                ? ($returnExtension ? $extUpload : true)
                : false;
        }
    }

}

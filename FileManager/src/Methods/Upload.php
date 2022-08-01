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


    /**
     * Check if the extension of he file that will be upload is in the
     * allowed extensions list. \
     * If `$returnExtension` is true, it will return a string containing the extension, otherwise a boolean.
     * 
     * @param array|string $file The file to check the extension
     * @param bool|int $returnExtension If `true`, will extension.
     * 
     * @return bool|never|string The extension or a boolean
     */

    public function checkExtension(array|string $file, bool|int $returnExtension = 0): bool|string
    {
        // Get ext
        $extUpload = strtolower(substr(strrchr($file, '.'), 1));

        if ($this->_allowed_extensions != []) {
            return in_array($extUpload, $this->_allowed_extensions)
                ? ($returnExtension ? $extUpload : true)
                : false;
        }

        (new Except())->error("Allowed extensions were not set.", 404);
    }


    /**
     * Upload files thanks to POST method (html forms).
     * 
     * By using the destination folder (you can also request to create one),
     * it will upload all files contained in the `$_FILES` (POST method).
     * If you want to upload a folder and also keep all the original paths,
     * set `true` (1) to the `$useDirectory` variable. But if the folder where
     * you want to upload the files isn't empty, it will return an error. If
     * you really want to upload files into a non-empty folder, you must set
     * `$ignoreDestContent` to true, but in any case, this function will remove
     * its old content.
     * 
     * @param string $destination 
     * @param bool|int $createDestination 
     * @param bool|int $useDirectories 
     * @param bool|int $ignoreDestContent 
     * 
     * @return void Anything
     */

    public function uploadByPost(
        string $destination,
        bool|int $createDestination = 0,
        bool|int $useDirectories = 0,
        bool|int $ignoreDestContent = 0
    ): void {
        define("DIR_SEPARATOR", self::DIR_SEPARATOR);

        // Destination dir
        if (is_dir($destination)) {
            if (!$ignoreDestContent && count(scandir($destination)) > 2)
                [new Except, "exception"]("Folder seems to already contain some files.");
        } else {
            if ($createDestination) {
                mkdir($destination);
            } else [new Except, "exception"]("Cannot read destination path.");
        }

        foreach ($_FILES as $file) {
            if ($file['size'] <= $this->_limit_file_size) {
                // Check ext

                if ($this->checkExtension($file['name'])) {
                    if ($useDirectories) {    
                        $getPath = function (string $var): array {
                            $obj = explode(DIR_SEPARATOR, $var);
                            $a = [];

                            foreach ($obj as $b)
                                array_push($a, $b);

                            return $a;
                        };

                        $convertToDirs = function (array $var): array {
                            if (sizeof($var) > 1) {
                                $a = [];

                                for ($b = 0; $b > sizeof($var); $b++) {
                                    for ($c = 0, $d = []; $c < $b + 1; $c++)
                                        array_push($d, $var[$c]);

                                    array_push($a, join(DIR_SEPARATOR, $d));
                                }

                                return $a;
                            }
                            else if (sizeof($var) == 1) return [$var[0]];
                            else return array("");
                        };

                        $a = $getPath($file['full_path']);

                        array_shift($a);
                        array_pop($a);

                        $dirs = $convertToDirs($a);

                        // Create folders
                        foreach ($dirs as $dir) {
                            $dir = Scan::readableFile($destination.DIR_SEPARATOR.$dir);

                            if (!is_dir($dir)) mkdir($dir);
                        }

                        $filePath = Scan::readableFile($destination.DIR_SEPARATOR.array_pop($dirs));
                    } else {
                        $filePath = Scan::readableFile($destination);
                    }

                    $filePath .= DIR_SEPARATOR.$file['name'];

                    // Upload file
                    move_uploaded_file($file['tmp_name'], $filePath);
                } // Invalid file's extension
            } // File too large
        }
    }

}

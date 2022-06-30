<?php

namespace FileManager\Methods;

use FileManager\Except;


/**
 * A FileManager aliases that can be used separately (or almost).
 * 
 * This part serves to index folders or files and give informations about it thanks
 * to a path.\
 * Currently, it gives informations only about the :
 *  - name
 *  - path
 *  - extension
 *  - the las modification date
 * 
 * If you want to use this script appart and use all functions on your own,
 * you have just to change the `\FileManager\Except` method that is used as an error handler.
 * 
 * @package FileManager
 */

class Scan {

    /**
     * Order the files by types.
     * @var string ORDER_BY_TYPES
     */

    public const ORDER_BY_TYPES = "OrderByTypes";


    /**
     * Order the files by types.
     * @var string ORDER_BY_NAME
     */
    
    public const ORDER_BY_NAME = "OrderByName";
    

    /**
     * Separate the files from the folders in an array.
     * @var string SEPARATE_FILES_FROM_FOLDERS
     */
    
    public const SEPARATE_FILES_FROM_FOLDERS = "SeparateFromFolder";


    /**
     * The path that will be use in this part of script.
     * This parameter may be changed in the `__construct` method.
     * 
     * @var string $path the main path that will be used.
     */

    public string $path;

    /**
     * This variable contains the indexed files.
     * It can't be access in public and it doesn't change even if you use the `OrderBy` method.
     * The only way to change his contents is to re-index files.
     * 
     * @var array $_indexed_files the files indexed.
     */

    protected array $_indexed_files;


    /**
     * Initializes the class.
     * 
     * Initialize a new instance.
     * Takes a path as parameter. This parameter is mandatory.
     * Checks for the existence of the concerned folder before any operation.
     * 
     * @param string $folder (a path)
     * 
     * @return void
     */
    
    public function __construct(string $folder)
    {
        // Use a readable value
        $this->path = $this::readableFile($folder);

        // Check the existence of the directory
        $this->dirExists();
    }


    /**
     * Check if a directory exists.
     * 
     * If the `$dir` parameter is empty, the program will check the existence of the `public $path`.
     * This function will return *`true`* if the directory exists but never return in other case.
     * 
     * @param ?string $dir the directory if you won't use the $path attribute.
     * 
     * @return bool|void Directory exists ?
     */

    public function dirExists(string $dir = ""): ?bool
    {
        // Use the local parameter `$path` if not specified.
        if (empty($dir)) $dir = $this->path;
        
        // Return *true* or "never"
        return is_readable($dir) || [new Except, 'exception']('Failed to open stream. No such file or directory.', 404);
    }


    /**
     * Return a readable directory path.
     * 
     * Return a readable directory path. In other word, that means this function
     * remove the "`//`" or replace the `\` with `/`.
     * If it's an array specified, it will return the array with each values changed.
     * 
     * @param array|string $dir a resource to rewrite.
     * 
     * @return array|string the readable directory value.
     */

    static public function readableFile (array|string $dir): array|string
    {
        /**
         * The closure at the heart of this function.
         * 
         * @param string $a
         * @return string
         */

        $replace = function (string $a) {
            return str_replace("//", "/", str_replace("\\", "/", $a));
        };

        // If it's only a string, return is readable value.
        if (is_string($dir))
            return $replace($dir);

        // Otherwise, do an array
        $array = [];

        // And implement the array with each readable path path.
        foreach ($dir as $folder)
            array_push($array, $replace($folder));
        
        // At least, return this array.
        return $array;
    }


    /**
     * Permits to save the response of the scan onto the `_indexed_files` property.
     * 
     * @param array $res
     * @return array $res
     */

    private function response(array $res): array {
        $this->_indexed_files = $res;
        return $res;
    }


    /**
     * Scan a given ressource but not recursively.
     * 
     * Thanks to a given path, or to the path property of this object,
     * return the list of resources found on a directory.\
     * This function isn't recursive ! If you want to use the recursivity,
     * try with the `recursiveScan`.
     * 
     * @param ?string $path a path of a directory to scan.
     * 
     * @return array result of the indexation.
     */
    
    public function scan(?string $path = ""): array
    {
        // Get the path if empty
        if (empty($path)) $path = $this->path;

        try {
            $storage = []; // The array containing the result
            $dir = @opendir($path); // Open stream

            // And while there is a value read into the directory, check it
            while ($file = readdir($dir)) {
                if (!in_array($file, array('..', '.'))) {
                    // And if the value are not a parent path, add the value to the final array
                    array_push($storage, $this::readableFile($path.DIRECTORY_SEPARATOR.$file));
                }
            }

            // Return the result after store it
            return $this->response($storage);
        } catch (\Throwable $th) {
            return [new Except, 'exception']($th);
        }
    }


    /**
     * Scan a given ressource recursively.
     * 
     * Thanks to a given path, or to the path property of this object,
     * return the list of resources found on a directory.\
     * This function is recursive, so if you only want to obtain a simple scan
     * of a direcotry try with the `recursiveScan`.
     * Due to the fact that you choose a recursive indexation, it can take more
     * time than usually if you have a lot of files.
     * 
     * @param ?string $path a path of a directory to scan recursively.
     * 
     * @return array the result of the indexation.
     */

    public function recursiveScan(?string $path = ""): array
    {
        // Get the path if empty
        if (empty($path)) $path = $this->path;

        try {
            $storage = []; // The array containing the result
            $dir = @opendir($path); // Open stream

            // While there's a value, do this
            while ($file = readdir($dir)) {
                // If not a parent path
                if (!in_array($file, array('..', '.'))) {
                    // get the current file readable file value
                    $currentFile = $this->readableFile($path.DIRECTORY_SEPARATOR.$file);

                    // add it to the final array
                    array_push($storage, $currentFile);
                    
                    // And if is a dir, do a recursive action with it values
                    if (is_dir($currentFile)) {
                        $tmp_storage = $this->recursiveScan($currentFile);

                        $storage = array_merge($storage, $tmp_storage);
                    }
                }
            }

            // Return the result after store it
            return $this->response($storage);
        } catch (\Throwable $e) {
            return [new Except, 'exception']($e);
        }
    }


    /**
     * Order files of an array by a specific method.
     * 
     * Orders the files in a given table using a specific method and a given order.
     * The method to order files can be strings, but it's prefered to use on of the constants
     * declared above. By default, the output is ascending but it can be change.
     * And if no order is specified, the files will be ordered by name (A to Z).
     * 
     * @param array|string &$files The array to re-order.
     * @param ?string $method The method to use to re-order files.
     * @param bool|int $ascend Basically reverse the array, but it's like we order it
     * in ascending or descending order (true/false)
     * 
     * @return string|never Return a string if it's string, beacause we can't order a string,
     * but we don't always return values (beacause it's useless).
     */

    public function orderBy(array|string &$files, ?string $method = "", bool|int $ascend = true): ?string
    {
        // Check if it's an array, beacause order string is useless XD
        if (is_array($files)) {
            // A little switch method to check the order
            switch ($method) {
                case $this::ORDER_BY_NAME:
                    return null;

                case $this::ORDER_BY_NAME:
                    return null;

                case $this::SEPARATE_FILES_FROM_FOLDERS:
                    $folders = [];

                    // Remove the folders from `$files` and store them into the variable `$folder`
                    foreach ($files as $key => $file) {
                        if (is_dir($file)) {
                            array_push($folders, $file);
                            unset($files[$key]);
                        }
                    }

                    // $files + $folders
                    $files = array_merge($files, $folders);

                    if (!$ascend)
                        $files = array_reverse($files);

                    break;

                default:
                    // By default, order files by name
                    return $this->orderBy($files, $this::ORDER_BY_NAME);
            }
        } else return null;
    }


    /**
     * Get file extension
     * 
     * Creates a new attribute named `size` for each files specified.
     * Compatible with `\FileManager\Methods\Scan $files` childs.
     * 
     * @param array|string &$files The file(s) to get the size
     */

    public function getFilesSize(array|string &$files)
    {
        if (is_string($files)) {
            $size = $this->size($files);
            $files = ['path' => $files];

            if (!is_null($size))
                $files['size'] = $size;

            return;
        }

        foreach ($files as &$file) {
            if (is_array($file)) {
                if (isset($file['path'])) {
                    $size = $this->size($file['path']);
                    
                    if (!is_null($size))
                        $file['extension'] = $size;
                } else {
                    [new Except, 'warning']("Unknown value");
                }
            } else if (isset($files['path'])) {
                $size = $this->size($files['path']);

                if (!is_null($size))
                    $files['size'] = $size;
                
                return;
            } else {
                $size = $this->size($file);
                $file = ['path' => $file];
    
                if (!is_null($size))
                    $file['size'] = $size;
            }
        }
    }


    /**
     * Get size of a directory
     * 
     * Return the size of a file as an integer.\
     * If the resource is a folder, it will return a `null` object.
     * However the values are controlled by the `getFilesSize` function;
     * 
     * @param string $file
     * 
     * @return int|null|never
     */

    private function size (string $file): ?int
    {
        if (file_exists($file)):
            return is_file($file) ? filesize($file) : null;
        else:
            return [new Except, 'warning']("{$file} not exist");
        endif;
    }


    /**
     * Get file extension
     * 
     * Return the extension of a file, or an array containing pathes.\
     * This won't work with folder, this is why no "extension" attribute will
     * be assigned to folders.\
     * Compatible with `\FileManager\Methods\Scan $files` childs.
     * 
     * @param array|string &$files The file(s) to get the extension
     * 
     * @return void|never
     */

    public function getFilesExtension(array|string &$files)
    {
        error_reporting(0);

        // String case (ex: "file.txt")
        if (is_string($files)) {
            $ext = $this->getExtension($files);
            $files = ['path' => $files];

            if (!is_null($ext))
                $files['extension'] = $ext;

            return;
        }

        // array cases
        foreach ($files as &$file) {
            if (is_array($file)) {
                // [0: ['path': 'file.txt']] => ['path': file.txt']
                if (isset($file['path'])) {
                    $ext = $this->getExtension($file['path']);
                    
                    if (!is_null($ext))
                        $file['extension'] = $ext;
                } else {
                    return [new Except, 'warning']("Unknown value");
                }
            } else if (isset($files['path'])) {
                // ['path': 'foo.php'] => 'foo.php'
                $ext = $this->getExtension($files['path']);

                if (!is_null($ext))
                    $files['extension'] = $ext;
                
                return;
            } else {
                // [0: 'bar.txt', ...] => 'bar.txt', ...
                $ext = $this->getExtension($file);
                $file = ['path' => $file];

                if (!is_null($ext))
                    $file['extension'] = $ext;
            }
        }

        error_reporting(-1);
    }


    /**
     * Get extension of a directory
     * 
     * Return a string containing the extension.\
     * If the resource is a folder, it will return a `null` object.
     * However the values are controlled by the `getFilesExtension` function;
     * 
     * @param string $file
     * 
     * @return string|null|never
     */

    private function getExtension(string $file): ?string
    {
        // If is file and it exists, return the extension
        if (file_exists($file)):
            return is_file($file)
                ? pathinfo($file)['extension']
                : null;
        else:
            return [new Except, 'warning']("{$file} not exist");
        endif;
    }


    /**
     * Get file name
     * 
     * Return the real name of a directory, or an array containing pathes.\
     * Compatible with `\FileManager\Methods\Scan $files` childs.
     * 
     * @param array|string &$files
     * 
     * @return void|never
     */

    public function getFilesName(array|string &$files)
    {
        if (is_string($files))
            return $files = ['path' => $files, 'name' => pathinfo($files)["basename"]];
        
        foreach ($files as &$file) {
            if (is_array($file))
                // [0: ['path': '../file.html' (, ...)] (, ...)]
                $file['name'] = pathinfo($file['path'])["basename"];
            else if (isset($files['path']))
                // ['path': 'foo' (, ...)]
                return $files['name'] = pathinfo($files['path'])["basename"];
            else
                // [0: 'file path' (, ...)]
                $file = ['path' => $file, 'name' => pathinfo($file)["basename"]];
        }
    }


    /**
     * Get last modifcation date
     * 
     * Return the last modification date of a file, or an array containing pathes.\
     * Compatible with `\FileManager\Methods\Scan $files` childs.
     * 
     * @param array|string &$files
     * 
     * @return void|never
     */

    public function getLastModificationDate(array|string &$files)
    {
        if (is_string($files))
            return $files = ['path' => $files, 'last_modification' => filemtime($files)];
        
        foreach ($files as &$file) {
            if (is_array($file))
                $file['last_modification'] = filemtime($file['path']);
            else if (isset($files['path']))
                return $files['extension'] = filemtime($files['path']);
            else
                $file = ['path' => $file, 'last_modification' => filemtime($file)];
        }
    }


    /**
     * Get file Content
     * 
     * Get the content of a file and return it.
     * If the file cannot be read or is not found, return his content.
     * 
     * @param string $file The file to return content
     * 
     * @return array|never Return array when file is read.
     */

    public function getFileContent(string $file): ?array
    {
        try {
            return is_readable($file)
                ? file($file)
                : [new Except, 'error']("File not found or cannot be read.", 404);
        } catch (\Throwable $th) {
            return [new Except, "exception"]($th);
        }
    }

}

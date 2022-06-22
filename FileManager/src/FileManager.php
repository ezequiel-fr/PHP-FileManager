<?php

namespace FileManager;

use FileManager\Methods\Destroy;
use FileManager\Methods\Scan;
use FileManager\Methods\Upload;

/**
 * FileManager - PHP managing files package \
 * PHP Version 8.0
 * 
 * FileManager is an open-source library that has many features related to file management.
 * For example, scanning folders, deleting, copying, ...
 * It manages files as well as folders.
 * 
 * This package is currently in development. Don't forget to check the Github regularly.
 * 
 * @package FileManager
 * @see https://github.com/TheRedMineTheRedMine
 * @author Ezequiel FRIDEL (TheRedMine TheRedMaths) <theredminedu51@gmail.com>
 * @copyright 2022 - 2027 Ezequiel Dev
 * @license MIT Liscence
 * @note This program is distributed in the hope that it will be useful \
 * WITHOUT ANY WARRANTY\
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

class FileManager extends Except {

    /** @var string $_realPath The path used by the programm bellow. */
    public string $_realPath;


    /**
     * Define if the programm have to send details.
     * Can be change thanks to the `getDetails()` function.\
     * By default, the value is `false`.
     * 
     * @var int|bool|false $_details
     */

    private int|bool $_details = false;

    
    /**
     * Create a new instance of the FileManager.
     * If the path is not specified, it'll be using the function `setDirectory`.
     * 
     * @param ?string $path The path that will be used.
     * 
     * @return void by convention.
     */

    public function __construct(?string $path = "")
    {
        // Here we set the path if specified.
        if (!empty($path))
            $this->setDirectory($path === "/" ? "./" : $path);
    }


    /**
     * When the function is called, it exchanges whether or not to display the details.
     * 
     * @return never
     */

    public function getDetails()
    {
        // Reverse the the situation :)
        $this->_details = !$this->_details;
    }
    

    /**
     * Set the path directory that will be used.
     * 
     * Set the path of the directory that will be used by the programm.
     * The path can also be declared into the *`__construct`* function.
     * 
     * @param string $path the path that will be used
     * 
     * @return never
     */
    
    public function setDirectory(string $path)
    {
        if (strlen(strstr(debug_backtrace()[0]["file"], "FileManager.php"))) {
            if (!empty($path))
                $this->_realPath = $path;
            else $this->error("Failed to declare the path. A non-empty value was expected.", 400);
        } else {
            // External program using `setDirectory`
            $this->_realPath = ($path === "/") ? "./" : $path;
        }
    }
    

    /**
     * Scan a provided directory.
     * 
     * Returns a complete scan of a folder (recursively or not). \
     * The `$recursive` value can be change to specify if recursivity must be used.
     * If the path is not specified before thanks to the `__construct` or the `setDirectory`
     * functions, it must be specified here.
     * 
     * PS : If you used this programm onto a folder filled with too much files or you used
     * recursivity, reponse time may be longer.
     * 
     * @param bool|int $recursive Recursive scanning or not
     * @param ?string $path A path can be specified.
     * 
     * @return array|void void is returned when something wrong happen.
     */
    
    public function scan(bool|int $recursive = false, ?string $path = ""): ?array
    {
        // Set the directory if something is specified
        if (!empty($path)) $this->setDirectory($path);
        
        if (isset($this->_realPath) && !empty($this->_realPath)) {
            // Create a new `Scan` instance
            $scan = new Scan($this->_realPath);
            
            // Call a scan function
            $result = [$scan, $recursive ? 'recursiveScan' : 'scan']();

            // Check if every files found are in readable format
            // (Normally not needed)
            Scan::readableFile($result);

            // If details are required, implement them
            if ($this->_details) {
                $scan->getFilesSize($result);
                $scan->getFilesExtension($result);
                $scan->getFilesName($result);
                $scan->getLastModificationDate($result);
            }
            
            // Return the result as an array (can be fetch)
            return $result;
        } else {
            // Send an error if the path is empty
            return $this->error("No path specified.");
        }
    }


    /**
     * Destroy a specific folder
     * 
     * Permanently deletes a directory whose path is specified by the `__construct`
     * or `setDirectory` function.
     * Returns an array filled with all booleans when the operation failed.
     * 
     * @return array|null null if there are no errors, otherwise send an array
     */

    public function destroy(): array
    {
        // Create a new instance
        $destroy = new Destroy();

        // Set the path and destroy his content
        $destroy->setPath($this->_realPath);
        $destroy->execute();

        // Send errors (if any :D)
        return $destroy->getErrors();
    }


    /**
     * Upload files from $_FILES
     * 
     * A feature in developpement that can upload files from $_FILES requests.
     * 
     * @param $content
     * @param string $allowedExtensions
     * 
     * @return Upload
     */

    public function upload($content, string $allowedExtensions = "../json/allowed_extensions.json"): Upload
    {
        $upload = new Upload($content, $allowedExtensions);

        return $upload;
    }


    /**
     * Send the content of the files
     * 
     * In developpement.
     * Currently just send human readable files (like: txt, html, md, ...)
     * 
     * @param array|string $file the file(s) to send
     */

    public function getFileContent(array|string $file)
    {
        if (is_string($file))
            return [new Scan($file), "getFileContent"]($file);
    }

}

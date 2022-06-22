<?php

namespace FileManager;

class Except {
    
    public function error (string $content, ?int $code = 0) {
        if ($code > 0) http_response_code($code);
        die(print("Error: ".$content));
    }

    
    public function warning ($content) {
        echo "warning \n";
        var_dump($content);
    }

    
    public function notice ($content) {
        echo "notice \n";
        var_dump($content);
    }

    
    public function deprecated ($content) {
        echo "deprecated \n";
        die(var_dump($content));
    }

    
    public function exception (string $content, ?int $code = 0) {
        if ($code > 0) http_response_code($code);
        die (print("ErrorException: ".$content));
    }
}

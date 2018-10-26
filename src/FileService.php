<?php
namespace Service;

use \Exception;

class FileService {

    /**
     * @param  string $pathToFile
     * @return array
     */
    public function fileBylines(string $pathToFile): array {
        $result=[];
        $file = $this->openFileForRead($pathToFile);
        $line = $this->fgets($file);
        if($line === false){
            throw new Exception ("File empty");
        }
        while ($line !== false){
            $result[] = $line;
            $line = $this->fgets($file);
        }
        $this->fclose($file);
        return $result;
    }

    private function  openFileForRead(string $pathToFile){
        $file = fopen($pathToFile, 'r');
        if($file === false){
            throw new Exception ("File did't find or can't be read");
        }
        $result = $file;
        return $result;
    }

    private function  fgets($file){
        return fgets($file);
    }

    private function fclose($file){
        return fclose($file);
    }
}
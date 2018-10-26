<?php
namespace Parser;

include 'FileService.php';

use Service\FileService;
use \Exception;

class Parser {

    private $_parserService;
    private $_lines;

    public function __construct(string $pathToFile){
        $parserService = new FileService();
        $this->_parserService = $parserService;
        $this->_readLog($pathToFile);
    }

    /**
     * @return array
    */
    public function parsLog(){
        $uniqueUrls = [];
        $traffic = 0;
        $crawlers = $this->_initializeCrawlers();
        $statusCodes = [];

        foreach ($this->_lines as $line){
            $uniqueUrls = $this->_addUrl($line, $uniqueUrls);
            $traffic += $this->_trafficFromLine($line, $traffic);
            $crawlers = $this->_addCrawler($line, $crawlers);
            $statusCodes = $this->_addStatusCode($line, $statusCodes);
        }

        $result = $this->_buildResult(
            [
                'views' => count($this->_lines),
                'urls' => count($uniqueUrls),
                'traffic' => $traffic,
                'crawlers' => $crawlers,
                'statusCodes' => $statusCodes
            ]
        );

        return $result;
    }

    /**
     * @param  string $pathToFile
     */
    private function _readLog(string $pathToFile)
    {
        try {
            $this->_lines = $this->_parserService->fileBylines($pathToFile);
        } catch (Exception $e) {
            throw new Exception("FileService", 0, $e);
        }
    }

    /**
     * @param  string $line
     * @param  array $uniqueUrls
     * @return array
     */
    private function _addUrl(string $line, array $uniqueUrls): array{
        $result = $uniqueUrls;
        $url = $this->_urlFromLine($line);
        if($url === null){
            return $result;
        }
        if (!array_key_exists($url, $result)) {
            $result[$url] = 1;
        }
        else $result[$url] += 1;
        return $result;
    }

    /**
     * @param  string $line
     * @param  array $crawlers
     * @return array
     */
    private function _addCrawler(string $line, array $crawlers): array{
        $result = $crawlers;
        $crawler = $this->_crawlerFromLine($line);
        if($crawler === null){
            return $result;
        }
        switch ($crawler){
            case 'googlebot':
                $result['Google'] += 1;
                break;
            case 'bingbot':
                $result['Bing'] += 1;
                break;
            case 'baidubot':
                $result['Baidu'] += 1;
                break;
            case 'yandexbot':
                $result['Yandex'] += 1;
                break;
            default:
                break;

        }
        return $result;
    }

    /**
     * @param  string $line
     * @param  array $uniqueCodes
     * @return array
     */
    private function _addStatusCode(string $line, array $uniqueCodes): array{
        $result = $uniqueCodes;
        $code = $this->_statusCodeFromLine($line);
        if($code === null){
            return $result;
        }
        if (!array_key_exists($code, $result)) {
            $result[$code] = 1;
        }
        else $result[$code] += 1;
        return $result;
    }

    /**
     * @param  string $line
     * @return integer
     */
    private function _trafficFromLine(string $line){
        $result = null;
        $matches = [];
        $trafficPattern = '/(- [0-9])? ([0-9]*) "(http|https):\/\//';
        $countOfMatches = preg_match($trafficPattern, $line, $matches);
        if($countOfMatches > 0){
            if(array_key_exists(1, $matches)){
                $isRedirect = !empty($matches[1]);
                if($isRedirect)
                    $result = 0;
                else if(array_key_exists(2, $matches))
                    $result = intval($matches[2]);
            }
        }
        return $result;
    }

    /**
     * @param  string $line
     * @return string
     */
    private function _urlFromLine(string $line){
        $result = null;
        $matches = [];
        $urlPattern = '/(POST|GET) ([\/\w \.-]*)*/';
        $countOfMatches =  preg_match($urlPattern, $line, $matches);
        if($countOfMatches > 0)
            $result = $matches[0];
        return $result;
    }

    /**
     * @param  string $line
     * @return string
     */
    private function _crawlerFromLine(string $line){
        $result = null;
        $matches = [];
        $urlPattern = '/\) ([a-z]*(bot))\/([0-9]*)\.([0-9]*) \(/i';
        $countOfMatches =  preg_match($urlPattern, $line, $matches);
        if($countOfMatches > 0)
            $result = strtolower($matches[1]);
        return $result;
    }

    /**
     * @param  string $line
     * @return string
     */
    private function _statusCodeFromLine(string $line){
        $result = null;
        $matches = [];
        $urlPattern = '/[0-9]" ([0-9]*) [0-9]*/';
        $countOfMatches =  preg_match($urlPattern, $line, $matches);
        if($countOfMatches > 0)
            $result = $matches[1];
        return $result;
    }

    /**
     * @return array
     */
    private function _initializeCrawlers(){
        $result = [];
        $result['Google'] = 0;
        $result['Bing'] = 0;
        $result['Baidu'] = 0;
        $result['Yandex'] = 0;
        return $result;
    }

    /**
     * @param array $params
     * @return array
     */
    private function _buildResult($params){
        $result = [];
        foreach ($params as $key=>$param){
            $result[$key] = $param;
        }
        return $result;
    }
}
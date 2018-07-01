<?php

namespace App\Models;

/**
 * Description of FileSystemFile
 *
 * @author oscar
 */
class FileSystemFile {

    private $file;
    private $nLine;
    private $items;

    public function __construct($filePath) {
        $this->file = new \SplFileObject($filePath);
        $this->nLine = 1;
        $this->items = array();
    }

    public function getTree() {
        $arrLevels = array();
        $arrItems = array();
        while (!$this->file->eof()) {
            $item = $this->getItem();
            $level = $item['level'];
            if(!isset($arrLevels[$level])){
                $arrLevels[$level] = array();
            }
            $arrItems[$this->nLine] = $item;
            $arrLevels[$level][] = $this->nLine;
            ++$this->nLine;
        }
        ksort($arrLevels);
        for($i = count($arrLevels) - 1; $i > 0; --$i){
            foreach ($arrLevels[$i] as $nLine){
                $parent = $this->searchParent($nLine, $arrLevels[$i - 1]);
                $arrItems[$nLine]['parent'] = $parent;
                $arrItems[$parent]['children'][] = $arrItems[$nLine];
                unset($arrItems[$nLine]);
            }
        }
        print_r($arrItems); 
        die();
    }

    private function searchParent($nLine, $parentLines){
        $parent = 0;
        foreach ($parentLines as $parentLine){
            if($parentLine > $nLine){
                break;
            }
            $parent = $parentLine;
        }
        return $parent;
    }

    private function getItem() {
        $line = $this->file->fgets();
        $name = trim($line);
        if (empty($name)) {
            return null;
        }
        $level = (strspn($line, ' ') / 4);
        $newItem = array(
            'id' => $this->nLine,
            'name' => $name,
            'level' => $level,
            'type' => 'ERROR',
            'parent' => 0
        );
        return $newItem;
    }

}

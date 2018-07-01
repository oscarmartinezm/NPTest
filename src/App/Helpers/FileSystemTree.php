<?php

namespace App\Helpers;

/**
 * Description of FileSystemFile
 *
 * @author oscar
 */
class FileSystemTree {

    public function __construct() {

    }

    public function getTree($data, $asArray = false) {
        if (empty($data)) {
            return array();
        }
        $parents = array();
        foreach ($data as $item) {
            $parents[$item['parent']][] = $item;
        }
        if ($asArray) {
            $tree = self::createBranchArr($parents, $parents[0]);
        } else {
            $tree = self::createBranchObj($parents, $parents[0]);
        }
        return $tree;
    }
    
    public function fromFile($filePath){
        $file = new \SplFileObject($filePath);
        $nLine = 1;
        $arrLevels = array();
        $arrItems = array();
        while (!$file->eof()) {
            $line = $file->fgets();
            $item = $this->getItem($line, $nLine);
            $level = $item['level'];
            if(!isset($arrLevels[$level])){
                $arrLevels[$level] = array();
            }
            $arrItems[$nLine] = $item;
            $arrLevels[$level][] = $nLine;
            ++$nLine;
        }
        //ksort($arrLevels);
        for($i = count($arrLevels) - 1; $i > 0; --$i){
            foreach ($arrLevels[$i] as $nLine){
                $parent = $this->searchParent($nLine, $arrLevels[$i - 1]);
                $arrItems[$nLine]['parent'] = $parent;
                $arrItems[$parent]['children'][] = $arrItems[$nLine];
                unset($arrItems[$nLine]);
            }
        }
        return $arrItems;
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

    private function getItem($line, $nLine) {
        $name = trim($line);
        if (empty($name)) {
            return null;
        }
        $level = (strspn($line, ' ') / 4);
        $newItem = array(
            'id' => $nLine,
            'name' => $name,
            'level' => $level,
            'parent' => 0
        );
        return $newItem;
    }
    
    private function createBranchArr(&$parents, $children) {
        $tree = array();
        foreach ($children as $child) {
            if (isset($parents[$child['id']])) {
                $child['children'] = $this->createBranchArr($parents, $parents[$child['id']]);
            }
            $tree[] = $child;
        }
        return $tree;
    }

    private function createBranchObj(&$parents, $children) {
        $tree = array();
        foreach ($children as $child) {
            $childObj = self::createFromDB($child);
            if (isset($parents[$child['id']])) {
                $childObj->children = $this->createBranchObj($parents, $parents[$child['id']]);
            }
            $tree[] = $childObj;
        }
        return $tree;
    }
    
    public static function get(){
        return new FileSystemTree();
    }

}

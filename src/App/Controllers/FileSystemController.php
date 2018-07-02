<?php

namespace App\Controllers;

use App\Models\FileSystem;

/**
 * Description of FileSystemController
 *
 * @author oscar
 */
class FileSystemController extends ControllerBase {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index() {
        $filesystem = FileSystem::getFlat(true);
        $this->loadView('filesystem/index', ['filesystem' => $filesystem]);
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create() {
        try {
            $directories = FileSystem::getFlat(false);
            $this->loadView('filesystem/form', ['directories' => $directories]);
        } catch (\Exception $exc) {
            $this->redirect('/filesystem/', $exc->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     */
    public function store() {
        try {
            $model = new FileSystem();
            $model->name = filter_input(INPUT_POST, 'name');
            $model->type = filter_input(INPUT_POST, 'type');
            $parent = filter_input(INPUT_POST, 'parent', FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => "/^[0-9]\-[0-9]$/")));
            if ($parent) {
                $parentSplit = explode('-', $parent);
                $model->parent = $parentSplit[0];
                $model->level = $parentSplit[1] + 1;
            }
            $model->save();
            $this->redirect('/filesystem/', null, "{$model->type} successfully saved!");
        } catch (\Exception $exc) {
            $this->redirect('/filesystem/add/', $exc->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id) {
        try {
            $model = FileSystem::find($id);
            $this->loadView('filesystem/form', ['item' => $model, '_error_' => self::$error]);
        } catch (\Exception $exc) {
            $this->redirect('/filesystem/', $exc->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id) {
        try {
            $model = FileSystem::find($id);
            $directories = FileSystem::getFlat(false);
            $this->loadView('filesystem/form', ['directories' => $directories, 'item' => $model, '_error_' => self::$error]);
        } catch (\Exception $exc) {
            $this->redirect('/filesystem/', $exc->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function update($id) {
        try {
            $model = FileSystem::find($id);
            $model->name = filter_input(INPUT_POST, 'name');
            $model->type = filter_input(INPUT_POST, 'type');
            $parent = filter_input(INPUT_POST, 'parent', FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => "/^[0-9]+\-[0-9]+$/")));
            if ($parent) {
                $parentSplit = explode('-', $parent);
                $model->parent = $parentSplit[0];
                $model->level = $parentSplit[1] + 1;
            }
            $model->save();
            $this->redirect('/filesystem/', null, "{$model->type} successfully saved!");
        } catch (\Exception $exc) {
            $this->redirect("/filesystem/update/{$id}/", $exc->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id) {
        try {
            FileSystem::delete($id);
            $this->redirect('/filesystem/');
        } catch (\Exception $exc) {
            $this->redirect('/filesystem/', $exc->getMessage());
        }
    }

    public function batch() {
        try {
            $initialParent = filter_input(INPUT_POST, 'parent', FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => "/^[0-9]+\-[0-9]+$/")));
            if (!$initialParent) {
                $this->redirect('/filesystem/add/', 'No parent information');
                exit();
            }
            if ($_FILES['file']['error'] != UPLOAD_ERR_OK || !is_uploaded_file($_FILES['file']['tmp_name'])) {
                $this->redirect('/filesystem/add/', 'There was an error uploading the file');
                exit();
            }
            $filePath = $_FILES['file']['tmp_name'];
            $truncate = filter_input(INPUT_POST, 'truncate');
            $initialParentData = explode('-', $initialParent);
            FileSystem::saveFromFile($filePath, $initialParentData[0], $initialParentData[1] + 1, $truncate);
            $this->redirect('/filesystem/');
        } catch (\Exception $exc) {
            $this->redirect('/filesystem/add/', $exc->getMessage());
        }
    }

    public static function get() {
        return new FileSystemController();
    }

}

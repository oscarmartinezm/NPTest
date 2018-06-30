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
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $filesystem = FileSystem::getFlat(true);
        $this->loadView('filesystem/index', ['filesystem' => $filesystem, '_error_' => $this->error]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        try {
            $directories = FileSystem::getFlat(false);
            $this->loadView('filesystem/form', ['directories' => $directories, '_error_' => $this->error]);
        } catch (\Exception $exc) {
            $this->redirect('/filesystem/', $exc->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
            $this->redirect('/filesystem/');
        } catch (\Exception $exc) {
            $this->redirect('/filesystem/add/', $exc->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        try {
            $model = FileSystem::find($id);
            $this->loadView('filesystem/form', ['item' => $model, '_error_' => $this->error]);
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
            $this->loadView('filesystem/form', ['directories' => $directories, 'item' => $model, '_error_' => $this->error]);
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
            $parent = filter_input(INPUT_POST, 'parent', FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => "/^[0-9]\-[0-9]$/")));
            if ($parent) {
                $parentSplit = explode('-', $parent);
                $model->parent = $parentSplit[0];
                $model->level = $parentSplit[1] + 1;
            }
            $model->save();
            $this->redirect('/filesystem/');
        } catch (\Exception $exc) {
            $this->redirect('/filesystem/add/', $exc->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        
    }

    public static function get() {
        return new FileSystemController();
    }

}

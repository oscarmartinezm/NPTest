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
        $this->loadView('filesystem/index', []);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(){
        $this->loadView('filesystem/form', []);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(){
        $model = new FileSystem();
        $model->name = filter_input(INPUT_POST, 'name');
        $model->type = filter_input(INPUT_POST, 'type');
        $model->parent = filter_input(INPUT_POST, 'parent');
        $model->save();
        echo 'Todo bien';
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(HotelForm $request, $id) {
        
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        
    }

    
}

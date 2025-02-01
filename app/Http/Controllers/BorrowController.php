<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BorrowController extends Controller
{
    //
    public function index(){
        return view('borrow.index');
    }
}

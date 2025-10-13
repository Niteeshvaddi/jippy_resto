<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct()
    {
//
    }

    public function privacypolicy()
    {
        return view('static.privacyandpolicy');
    }

    public function deleteaccount()
    {
        return view('static.deleteaccount');
    }
}

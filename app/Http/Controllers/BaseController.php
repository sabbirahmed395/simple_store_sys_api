<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected $_per_page = 10;

    public function __construct()
    {
        if (request()->has('per_page') && intval(request()->per_page) != 0) {
            $this->_per_page = intval(request()->per_page);
        }
    }
}

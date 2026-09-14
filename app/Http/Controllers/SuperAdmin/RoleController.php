<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    public function index()
    {
        return view('superadmin.roles.index');
    }
}

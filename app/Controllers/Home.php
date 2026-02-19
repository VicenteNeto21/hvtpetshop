<?php

namespace App\Controllers;

class Home extends BaseController
{
    protected bool $skipAuth = true;

    public function index(): string
    {
        return view('welcome_message');
    }
}

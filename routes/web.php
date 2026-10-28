<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'orders.create');

Route::view('/orders/new', 'orders.create');
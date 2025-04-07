<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::view('/', 'pages.dashboard')->name('dashboard');
Route::view('/dashboard', 'pages.dashboard');
Route::view('/friends', 'pages.friends');
Route::view('/recomanacions', 'pages.recomanacions');
Route::view('/ranking', 'pages.ranking');

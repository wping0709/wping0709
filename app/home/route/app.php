<?php
use think\facade\Route;
Route::post('data', 'data');
Route::get('detail/:id', 'detail');
Route::get('archives/:id', 'archives');
Route::get('archives', 'archives');
Route::get('album/:id', 'photo');
Route::get('album', 'album');
Route::get('about', 'about');
Route::get('guestbook', 'guestbook');
Route::get('link', 'link');
Route::get('search', 'search');
Route::get('sitemap', 'sitemap');
Route::rule('manage/login', 'home/login/index');

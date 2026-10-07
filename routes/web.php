<?php

use App\Http\Controllers\GardenController as Garden;
use Illuminate\Support\Facades\Route;

Route::redirect('/login', '/admin/login')->name('login');
Route::get('/', [Garden::class, 'home'])->name('home');
Route::get('/explore', [Garden::class, 'explore'])->name('explore');
Route::get('/work', [Garden::class, 'explore'])->name('work');
Route::get('/blogs', [Garden::class, 'explore'])->name('blogs');
Route::get('/branches/{slug}', [Garden::class, 'branch'])->name('branch');
Route::get('/channels', [Garden::class, 'channels'])->name('channels');
Route::get('/channels/{slug}', [Garden::class, 'channel'])->name('channel');
Route::get('/series/{slug}', [Garden::class, 'series'])->name('series');
Route::view('/now', 'garden.now')->name('now');
Route::view('/contact', 'garden.contact')->name('contact');
Route::get('/entries/{slug}', [Garden::class, 'entry'])->name('entry');
Route::get('/preview/{entry}', [Garden::class, 'preview'])->middleware(['auth', 'signed'])->name('entry.preview');
Route::get('/media/{media}', [Garden::class, 'media'])->name('media');
Route::get('/sitemap.xml', [Garden::class, 'sitemap'])->name('sitemap');

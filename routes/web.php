<?php

use App\Http\Controllers\AdminInvitationAcceptanceController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicVideoStreamController;
use Illuminate\Support\Facades\Route;

Route::get('/login', static fn() => abort(404));
Route::get('/register', static fn() => abort(404));
Route::get('/forgot-password', static fn() => abort(404));
Route::get('/email/verify', static fn() => abort(404));
Route::get('/two-factor-challenge', static fn() => abort(404));
Route::get('/user/confirm-password', static fn() => abort(404));

Route::get('/', PublicHomeController::class)->name('home');
Route::get('/videos/{video}/stream', PublicVideoStreamController::class)
    ->name('videos.stream');

Route::get('/admin/invitations/{token}', [AdminInvitationAcceptanceController::class, 'show'])
    ->name('admin.invitations.show');

Route::post('/admin/invitations/{token}', [AdminInvitationAcceptanceController::class, 'store'])
    ->name('admin.invitations.store');

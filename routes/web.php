<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Serves the Blade UI pages. Authentication is handled client-side via
| localStorage tokens that call /api/* on this same application.
*/

// ── Auth ──────────────────────────────────────────────────────────────────────

Route::get('/login',  fn () => view('pages.login'))->name('login');
Route::get('/logout', fn () => view('pages.logout'))->name('logout');

// Redirect root to dashboard
Route::get('/', fn () => redirect('/dashboard'));

// ── App Pages ─────────────────────────────────────────────────────────────────

Route::get('/dashboard',  fn () => view('pages.dashboard'))->name('dashboard');
Route::get('/borrowers',  fn () => view('pages.borrowers'))->name('borrowers');
Route::get('/loans',      fn () => view('pages.loans'))->name('loans');
Route::get('/payments',   fn () => view('pages.payments'))->name('payments');
Route::get('/collateral', fn () => view('pages.collateral'))->name('collateral');
Route::get('/calendar',   fn () => view('pages.calendar'))->name('calendar');
Route::get('/overdue',    fn () => view('pages.overdue'))->name('overdue');
Route::get('/reports',    fn () => view('pages.reports'))->name('reports');
Route::get('/settings',   fn () => view('pages.settings'))->name('settings');

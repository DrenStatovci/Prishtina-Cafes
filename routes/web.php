<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', fn() => inertia('Welcome'))->name('home'); // public


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn() => inertia('Dashboard'))->name('dashboard');     // protected
    Route::get('/menu', fn() => inertia('Menu/Index'))->name('menu');
    Route::get('/my-orders', fn() => inertia('MyOrders'))->name('my-orders');     // customer orders
    Route::get('/staff/dashboard', function () {
        return Inertia::render('Staff/Dashboard');
    })->name('staff.dashboard');
});

Route::middleware(['auth', 'verified', 'role:owner|manager|waiter|bartender|admin'])
    ->get('/staff/orders', fn() => inertia('Staff/Orders'))
    ->name('staff.orders');

// Staff Manage (landing + pages)
Route::middleware(['auth', 'verified', 'role:admin|owner|manager'])->group(function () {
    Route::get('/staff/manage', fn() => inertia('Staff/Manage/Index'))
        ->name('staff.manage');
    Route::get('/staff/manage/products', fn() => inertia('Staff/Manage/Products'))
        ->name('staff.manage.products');
    Route::get('/staff/manage/categories', fn() => inertia('Staff/Manage/Categories'))
        ->name('staff.manage.categories');
    Route::get('/staff/manage/branches', fn() => inertia('Staff/Manage/Branches'))
        ->name('staff.manage.branches');
    Route::get('/staff/manage/cafes', fn() => inertia('Staff/Manage/Cafes'))
        ->name('staff.manage.cafes');
    Route::get('/staff/manage/staff', fn() => inertia('Staff/Manage/Staff'))
        ->name('staff.manage.staff');
});


// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });


require __DIR__ . '/auth.php';

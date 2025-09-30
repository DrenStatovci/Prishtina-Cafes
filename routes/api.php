<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MockPaymentController;
use App\Http\Controllers\PublicCafeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CafeController;
use App\Http\Controllers\StaffProfileController;


// Public read-only
Route::get('/cafes', [PublicCafeController::class, 'cafes']);
Route::get('/cafes/{slug}/branches', [PublicCafeController::class, 'branches']);
Route::get('/cafes/{slug}/categories', [PublicCafeController::class, 'categories']);
Route::get('/categories/{slug}/products', [PublicCafeController::class, 'products']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(\Illuminate\Http\Request $r) => $r->user());

    // Available users for assignment
    Route::get('/users/available-owners', function (\Illuminate\Http\Request $r) {
        if (!$r->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return \App\Models\User::select('id', 'name', 'email')->get();
    });

    Route::get('/users/available-for-staff', function (\Illuminate\Http\Request $r) {
        if (!$r->user()->hasRole(['admin', 'owner', 'manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Return all users - they can be assigned to multiple cafes/positions
        // The staff profile creation will handle duplicates/conflicts
        return \App\Models\User::select('id', 'name', 'email')->get();
    });

    // Create new user
    Route::post('/users', function (\Illuminate\Http\Request $r) {
        if (!$r->user()->hasRole(['admin', 'owner', 'manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:8',
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Hash::make($validated['password'] ?? \Str::random(12)),
            'email_verified_at' => now(),
        ]);

        return response()->json($user, 201);
    });

    // Manage namespace (scoped in controllers/policies)
    Route::prefix('manage')->group(function () {
        // Cafes
        Route::get('/cafes', [CafeController::class, 'index']);
        Route::post('/cafes', [CafeController::class, 'store']);
        Route::put('/cafes/{cafe}', [CafeController::class, 'update']);
        Route::get('/cafes/{cafe}', [CafeController::class, 'show']);
        Route::patch('/cafes/{cafe}/toggle', [CafeController::class, 'toggle']);

        // Branches
        Route::get('/branches', [BranchController::class, 'index']);
        Route::post('/branches', [BranchController::class, 'store']);
        Route::put('/branches/{branch}', [BranchController::class, 'update']);
        Route::get('/branches/{branch}', [BranchController::class, 'show']);
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy']);
        Route::patch('/branches/{branch}/toggle', [BranchController::class, 'toggle']);

        // Categories
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::get('/categories/{category}', [CategoryController::class, 'show']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
        Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggle']);

        // Products
        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        Route::patch('/products/{product}/toggle', [ProductController::class, 'toggle']);

        // Staff Profiles
        Route::get('/staff', [StaffProfileController::class, 'index']);
        Route::post('/staff/profiles', [StaffProfileController::class, 'store']);
        Route::put('/staff/profiles/{staffProfile}', [StaffProfileController::class, 'update']);
        Route::get('/staff/profiles/{staffProfile}', [StaffProfileController::class, 'show']);
        Route::delete('/staff/profiles/{staffProfile}', [StaffProfileController::class, 'destroy']);
        Route::patch('/staff/profiles/{staffProfile}/toggle', [StaffProfileController::class, 'toggle']);
    });

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']); // Customer orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->middleware('can:updateStatus,order');

    // Mock payment
    Route::post('/orders/{order}/pay-mock', [MockPaymentController::class, 'store'])
        ->middleware('can:pay,order');
});

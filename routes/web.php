<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Controllers\TestController;
use App\Http\Controllers\PlantScanEmailController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PetController;
use App\Http\Controllers\Admin\PlantController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    return view('home2');
});

Route::get('/plantscan', function () {
    return view('plantscan');
});

// Public API: fetch plant description by name or slug
Route::get('/plants/description', [App\Http\Controllers\PlantDescriptionController::class, 'show']);

// Original home page
Route::get('/home2', function () {
    return view('home');
});

Route::get('/urna', function () {
    return view('urna');
});

// Plant catalog (public)
Route::get('/plantas', function () {
    $plants = \App\Models\Plant::where('is_active', true)
        ->orderBy('name', 'asc')
        ->get();
    return view('plants.catalog', compact('plants'));
})->name('plants.catalog');

Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Tests endpoint used by PlantScan frontend
Route::post('/tests', [TestController::class, 'store']);

// PlantScan email endpoint (with image attachment)
Route::post('/plantscan/email', [PlantScanEmailController::class, 'send']);

// Public permalink for plant scan results
Route::get('/plantscan/result/{token}', [App\Http\Controllers\PlantScanResultController::class, 'show']);

// Public profile pages
Route::get('/profile/{slug}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/profile/id/{id}', [ProfileController::class, 'showById'])->name('profile.showById');

// Protected admin pages (use EnsureAdmin middleware)
Route::middleware([EnsureAdmin::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/tests', [AdminController::class, 'tests']);
    Route::get('/admin/clients', [AdminController::class, 'clients'])->name('admin.clients');
    Route::post('/admin/clients/create', [AdminController::class, 'createClient']);
    Route::post('/admin/clients/delete-multiple', [AdminController::class, 'deleteMultipleClients']);
    Route::get('/admin/clients/{id}/edit', [ClientController::class, 'edit'])->name('admin.clients.edit');
    Route::post('/admin/clients/{id}/update', [ClientController::class, 'update'])->name('admin.clients.update');
    
    // Pet management
    Route::get('/admin/pets/{id}/edit', [PetController::class, 'edit'])->name('admin.pets.edit');
    Route::post('/admin/pets/{id}/update', [PetController::class, 'update'])->name('admin.pets.update');
    Route::post('/admin/pets/{id}/delete-photo', [PetController::class, 'deletePhoto'])->name('admin.pets.deletePhoto');
    
    // Plant management
    Route::get('/admin/plants', [AdminController::class, 'plants'])->name('admin.plants');
    Route::post('/admin/plants/create', [AdminController::class, 'createPlant']);
    Route::post('/admin/plants/delete-multiple', [AdminController::class, 'deleteMultiplePlants']);
    Route::get('/admin/plants/{id}/edit', [PlantController::class, 'edit'])->name('admin.plants.edit');
    Route::post('/admin/plants/{id}/update', [PlantController::class, 'update'])->name('admin.plants.update');
    Route::post('/admin/plants/{id}/delete-photo', [PlantController::class, 'deletePhoto'])->name('admin.plants.deletePhoto');

    // Admin image generator (MVP)
    Route::get('/admin/images/generator', [\App\Http\Controllers\Admin\ImageGeneratorController::class, 'index'])->name('admin.images.generator');
    Route::post('/admin/images/generator/upload', [\App\Http\Controllers\Admin\ImageGeneratorController::class, 'upload'])->name('admin.images.upload');
    Route::post('/admin/images/generator/server', [\App\Http\Controllers\Admin\ImageGeneratorController::class, 'server'])->name('admin.images.server');
    
    // User management
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/admin/users/create', [UserController::class, 'store'])->name('admin.users.create');
    Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{id}/update', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}/delete', [UserController::class, 'destroy'])->name('admin.users.delete');
    Route::post('/admin/users/delete-multiple', [UserController::class, 'deleteMultiple'])->name('admin.users.deleteMultiple');
    
    // Admin settings
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings/profile', [AdminController::class, 'updateProfile'])->name('admin.settings.profile');
    Route::post('/admin/settings/password', [AdminController::class, 'updatePassword'])->name('admin.settings.password');
    
    // Seeder route (temporary - remove after use)
    Route::get('/admin/run-plants-seeder', function () {
        try {
            \Artisan::call('db:seed', ['--class' => 'UpdatePlantsDetailsSeeder']);
            $output = \Artisan::output();
            return '<pre>' . $output . '</pre>';
        } catch (\Exception $e) {
            return '<pre>Error: ' . $e->getMessage() . '</pre>';
        }
    })->name('admin.run.seeder');
});

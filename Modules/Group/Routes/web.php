<?php

use Illuminate\Support\Facades\Route;
use Modules\Group\Http\Controllers\GroupController;

Route::middleware('auth')->group(function (): void {
    Route::prefix('groups')->name('groups.')->group(function (): void {
        // Main group routes
        Route::get('/', [GroupController::class, 'index'])->name('index');
        Route::get('/api/list', [GroupController::class, 'listJson'])->name('list');
        Route::get('/create', [GroupController::class, 'create'])->name('create');
        Route::post('/', [GroupController::class, 'store'])->name('store');
        Route::get('/{group}', [GroupController::class, 'show'])->name('show');
        Route::get('/{group}/edit', [GroupController::class, 'edit'])->name('edit');
        Route::put('/{group}', [GroupController::class, 'update'])->name('update');
        Route::delete('/{group}', [GroupController::class, 'destroy'])->name('destroy');

        // Course management
        Route::put('/{group}/courses', [GroupController::class, 'updateCourses'])->name('courses.update');
        Route::get('/{group}/courses/available', [GroupController::class, 'getAvailableCourses'])->name('courses.available');
        Route::get('/{group}/courses/assigned', [GroupController::class, 'getAssignedCourses'])->name('courses.assigned');
        Route::post('/{group}/courses/{courseId}', [GroupController::class, 'addCourse'])->name('courses.add');
        Route::delete('/{group}/courses/{courseId}', [GroupController::class, 'removeCourse'])->name('courses.remove');

        // User management
        Route::post('/{group}/users', [GroupController::class, 'assignUsers'])->name('users.assign');
        Route::delete('/{group}/users', [GroupController::class, 'removeUser'])->name('users.remove');
        Route::get('/{group}/users/available', [GroupController::class, 'getAvailableUsers'])->name('users.available');
        Route::get('/{group}/users', [GroupController::class, 'getMembers'])->name('users.members');
        Route::put('/{group}/users/{userId}/role/{role}', [GroupController::class, 'updateUserRole'])->name('users.role');

        // Statistics
        Route::get('/{group}/stats', [GroupController::class, 'getStats'])->name('stats');
        Route::get('/{group}/programmes', [GroupController::class, 'getProgammes'])->name('programmes');
    });
});

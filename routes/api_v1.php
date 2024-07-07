<?php

use App\Http\Controllers\Api\V1\AdministratorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthenticationController;
use App\Http\Controllers\Api\V1\LogSessionController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\MenuGroupController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\PermissionGroupController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;

Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/change-password', [AuthenticationController::class, 'changePassword']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/authenticated', [AuthenticationController::class, 'authenticated']);
    Route::post('/logout', [AuthenticationController::class, 'logout']);

    # Log Session 
    Route::get('log-session/{user_id}', [LogSessionController::class, 'allSessionUser']);
    Route::delete('log-session/{user_id}/user', [LogSessionController::class, 'destroyUser']);
    Route::delete('log-session/{id}/token', [LogSessionController::class, 'destroyToken']);
    # Menu
    Route::get('menu-list-all', [MenuController::class, 'menuListWithParent']);
    Route::get('menu-list', [MenuController::class, 'menuList']);
    Route::get('menu-parent-arr', [MenuController::class, 'parentMenu']);
    Route::apiResource('menu', MenuController::class);

    # Menu Group
    Route::get('menu-group-arr', [MenuGroupController::class, 'menuGroupArr']);
    Route::apiResource('menu-group', MenuGroupController::class);

    # Role
    Route::get('role-arr', [RoleController::class, 'listArr']);
    Route::get('role-id-arr', [RoleController::class, 'listIdArr']);
    Route::get('role/{name}/users', [RoleController::class, 'roleUser']);
    Route::apiResource('role', RoleController::class);

    # Permission
    Route::get('permission-list', [PermissionController::class, "permissionList"]);
    Route::apiResource('permission', PermissionController::class);

    # Permission Group
    Route::get('permission-group-all', [PermissionGroupController::class, 'groupWithPermissions']);
    Route::get('permission-group-arr', [PermissionGroupController::class, 'listArr']);
    Route::apiResource('permission-group', PermissionGroupController::class);

    # User
    Route::get('user/profile', [UserController::class, 'profile']);
    Route::match(['put'], 'user/profile', [UserController::class, 'updateProfile']);
    Route::match(['put'], 'user/profile-username', [UserController::class, 'updateProfileUsername']);
    Route::match(['put'], 'user/profile-email', [UserController::class, 'updateProfileEmail']);
    Route::match(['put'], 'user/profile-password', [UserController::class, 'updateProfilePassword']);
    Route::post('user/deactivate', [UserController::class, 'deactivate']);

    # Administrator
    Route::match(['put'], 'administrator/{id}/restore', [AdministratorController::class, 'restore']);
    Route::delete('administrator/{id}/delete', [AdministratorController::class, 'delete']);
    Route::apiResource('administrator', AdministratorController::class);
});

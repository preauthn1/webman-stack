<?php

declare(strict_types=1);

use Webman\Route;
use app\admin\controller\AuthController;
use app\admin\controller\AdminController;
use app\admin\controller\RoleController;
use app\admin\controller\PermissionController;
use app\admin\controller\ArticleController as AdminArticleController;
use app\admin\controller\CategoryController;
use app\admin\controller\SettingController;
use app\admin\controller\LogController;
use app\admin\controller\DashboardController;
use app\admin\controller\UploadController;
use app\admin\controller\IpWhitelistController;
use app\admin\controller\DictController;
use app\admin\controller\NotificationController;
use app\admin\controller\DepartmentController;
use app\middleware\AuthMiddleware;
use app\middleware\AdminLogMiddleware;
use app\middleware\IpWhitelistMiddleware;

// CORS 预检请求（OPTIONS 不走路由匹配时中间件不会执行，需显式注册）
Route::options('/admin/{path:.+}', fn () => response('', 204));

// 无需认证
Route::get('/admin/captcha', [AuthController::class, 'captcha'])->middleware([IpWhitelistMiddleware::class]);
Route::get('/admin/login-status', [AuthController::class, 'loginStatus'])->middleware([IpWhitelistMiddleware::class]);
Route::post('/admin/login', [AuthController::class, 'login'])->middleware([IpWhitelistMiddleware::class]);
Route::post('/admin/refresh', [AuthController::class, 'refresh'])->middleware([IpWhitelistMiddleware::class]);

// 需要认证
Route::group('/admin', function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/menus', [AuthController::class, 'menus']);

    Route::get('/dashboard/total', [DashboardController::class, 'total']);
    Route::get('/dashboard/userAccessSource', [DashboardController::class, 'userAccessSource']);
    Route::get('/dashboard/weeklyUserActivity', [DashboardController::class, 'weeklyUserActivity']);
    Route::get('/dashboard/monthlySales', [DashboardController::class, 'monthlySales']);

    Route::get('/admins', [AdminController::class, 'index']);
    Route::post('/admins', [AdminController::class, 'store']);
    Route::put('/admins/{id:\d+}', [AdminController::class, 'update']);
    Route::delete('/admins/{id:\d+}', [AdminController::class, 'destroy']);
    Route::put('/admins/{id:\d+}/password', [AdminController::class, 'updatePassword']);
    Route::post('/admins/{id:\d+}/revoke-sessions', [AdminController::class, 'revokeSessions']);

    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{id:\d+}', [RoleController::class, 'update']);
    Route::delete('/roles/{id:\d+}', [RoleController::class, 'destroy']);
    Route::get('/roles/{id:\d+}/permissions', [RoleController::class, 'permissions']);
    Route::put('/roles/{id:\d+}/permissions', [RoleController::class, 'assignPermissions']);

    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::put('/permissions/{id:\d+}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{id:\d+}', [PermissionController::class, 'destroy']);

    Route::get('/articles', [AdminArticleController::class, 'index']);
    Route::get('/articles/export', [AdminArticleController::class, 'export']);
    Route::post('/articles/import', [AdminArticleController::class, 'import']);
    Route::get('/articles/{id:\d+}', [AdminArticleController::class, 'show']);
    Route::post('/articles', [AdminArticleController::class, 'store']);
    Route::put('/articles/{id:\d+}', [AdminArticleController::class, 'update']);
    Route::delete('/articles/{id:\d+}', [AdminArticleController::class, 'destroy']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id:\d+}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id:\d+}', [CategoryController::class, 'destroy']);

    Route::get('/settings', [SettingController::class, 'index']);
    Route::put('/settings', [SettingController::class, 'batchUpdate']);
    Route::get('/settings/{key}', [SettingController::class, 'show']);
    Route::put('/settings/{key}', [SettingController::class, 'update']);

    Route::get('/logs', [LogController::class, 'index']);

    Route::post('/upload/presign', [UploadController::class, 'presign']);

    Route::get('/ip-whitelists', [IpWhitelistController::class, 'index']);
    Route::post('/ip-whitelists', [IpWhitelistController::class, 'store']);
    Route::put('/ip-whitelists/{id:\d+}', [IpWhitelistController::class, 'update']);
    Route::delete('/ip-whitelists/{id:\d+}', [IpWhitelistController::class, 'destroy']);

    Route::get('/dicts', [DictController::class, 'index']);
    Route::post('/dicts', [DictController::class, 'store']);
    Route::put('/dicts/{id:\d+}', [DictController::class, 'update']);
    Route::delete('/dicts/{id:\d+}', [DictController::class, 'destroy']);
    Route::get('/dicts/{id:\d+}/items', [DictController::class, 'items']);
    Route::put('/dicts/{id:\d+}/items', [DictController::class, 'saveItems']);
    Route::get('/dicts/code/{code}', [DictController::class, 'byCode']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::put('/notifications/{id:\d+}/read', [NotificationController::class, 'markRead']);

    Route::get('/departments/tree', [DepartmentController::class, 'tree']);
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::put('/departments/{id:\d+}', [DepartmentController::class, 'update']);
    Route::delete('/departments/{id:\d+}', [DepartmentController::class, 'destroy']);
})->middleware([IpWhitelistMiddleware::class, AuthMiddleware::class, AdminLogMiddleware::class]);

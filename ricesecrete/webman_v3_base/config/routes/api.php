<?php

declare(strict_types=1);

use Webman\Route;
use app\api\controller\ArticleController as ApiArticleController;
use app\api\controller\UploadController as ApiUploadController;
use app\middleware\AuthMiddleware;

// CORS 预检请求（OPTIONS 不走路由匹配时中间件不会执行，需显式注册）
Route::options('/api/{path:.+}', fn () => response('', 204));

// 公共 API
Route::get('/api/articles', [ApiArticleController::class, 'index']);
Route::get('/api/articles/{id:\d+}', [ApiArticleController::class, 'show']);

// 需登录，上传接口走白名单（不校验 API 权限）
Route::post('/api/upload', [ApiUploadController::class, 'store'])
    ->middleware([AuthMiddleware::class]);

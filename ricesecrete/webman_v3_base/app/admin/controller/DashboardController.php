<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\controller\concerns\DefinesAdminMenu;
use support\Request;
use app\model\Admin\AdminModel;
use app\model\Content\ArticleModel;
use app\model\Content\CategoryModel;
use app\model\Monitor\AdminLogModel;

class DashboardController extends BaseController
{
    use DefinesAdminMenu;

public static function menuConfig(): ?array
    {
        return [
            'group' => [
                'name' => '控制台',
                'slug' => 'console',
                'path' => '/console',
                'icon' => 'dashboard',
                'sort' => 0,
                'children' => [
                    [
                        'name' => '分析页',
                        'slug' => 'dashboard',
                        'path' => '/console/dashboard',
                        'icon' => 'dashboard',
                        'component' => 'views/dashboard/index',
                        'sort' => 1,
                    ],
                ],
            ],
        ];
    }

    public function total(Request $request)
    {
        return success([
            'users' => AdminModel::where('status', 1)->count(),
            'messages' => ArticleModel::count(),
            'moneys' => CategoryModel::count(),
            'shoppings' => AdminLogModel::count(),
        ]);
    }

    public function userAccessSource(Request $request)
    {
        return success([
            ['name' => 'analysis.directAccess', 'value' => 320],
            ['name' => 'analysis.mailMarketing', 'value' => 120],
            ['name' => 'analysis.allianceAdvertising', 'value' => 220],
            ['name' => 'analysis.videoAdvertising', 'value' => 150],
        ]);
    }

    public function weeklyUserActivity(Request $request)
    {
        return success([
            ['name' => 'analysis.monday', 'value' => 12],
            ['name' => 'analysis.tuesday', 'value' => 18],
            ['name' => 'analysis.wednesday', 'value' => 15],
            ['name' => 'analysis.thursday', 'value' => 22],
            ['name' => 'analysis.friday', 'value' => 28],
            ['name' => 'analysis.saturday', 'value' => 10],
            ['name' => 'analysis.sunday', 'value' => 8],
        ]);
    }

    public function monthlySales(Request $request)
    {
        return success([
            ['name' => 'analysis.january', 'estimate' => 120, 'actual' => 130],
            ['name' => 'analysis.february', 'estimate' => 110, 'actual' => 105],
            ['name' => 'analysis.march', 'estimate' => 140, 'actual' => 150],
            ['name' => 'analysis.april', 'estimate' => 100, 'actual' => 95],
            ['name' => 'analysis.may', 'estimate' => 160, 'actual' => 170],
            ['name' => 'analysis.june', 'estimate' => 150, 'actual' => 155],
        ]);
    }
}

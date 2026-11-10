<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\controller\concerns\DefinesAdminMenu;
use support\Request;
use app\service\SettingService;

class SettingController extends BaseController
{
    use DefinesAdminMenu;

public static function menuConfig(): ?array
    {
        return [
            'group' => [
                'name' => '系统配置',
                'slug' => 'system-config',
                'path' => '/system-config',
                'icon' => 'setting',
                'sort' => 30,
            ],
            'menu' => [
                'name' => '参数配置',
                'slug' => 'setting-menu',
                'path' => '/system-config/setting',
                'icon' => 'tools',
                'component' => 'views/system/setting/index',
                'sort' => 1,
            ],
            'apis' => [
                ['name' => '配置列表', 'slug' => 'setting:list', 'path' => '/admin/settings', 'method' => 'GET', 'sort' => 1],
                ['name' => '更新配置', 'slug' => 'setting:update', 'path' => '/admin/settings/*', 'method' => 'PUT', 'sort' => 2],
            ],
        ];
    }

    protected SettingService $settingService;

    public function __construct(?SettingService $settingService = null)
    {
        $this->settingService = $settingService ?? new SettingService();
    }

    public function index(Request $request)
    {
        return success($this->settingService->list());
    }

    public function show(Request $request, string $key)
    {
        return success($this->settingService->get($key));
    }

    public function update(Request $request, string $key)
    {
        $value = $request->post('value');
        return success($this->settingService->update($key, $value), '更新成功');
    }

    public function batchUpdate(Request $request)
    {
        $settings = $request->post('settings', []);
        $this->settingService->batchUpdate($settings);
        return success(null, '批量更新成功');
    }
}

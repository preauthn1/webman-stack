<?php

declare(strict_types=1);

namespace database\support;

use Illuminate\Database\Capsule\Manager as DB;

class PermissionSeeder
{
    public static function assignEditorPermissions(int $editorRoleId): void
    {
        $data = require __DIR__ . '/../data/permissions.php';
        foreach ($data['editor_api_slugs'] as $slug) {
            $permId = DB::table('permissions')
                ->where('type', 'api')
                ->where('slug', $slug)
                ->value('id');

            if (!$permId) {
                continue;
            }

            DB::table('role_permissions')->insert([
                'role_id' => $editorRoleId,
                'permission_id' => $permId,
            ]);
        }
    }

    public static function clearRedisCache(): void
    {
        $adminIds = DB::table('admins')->pluck('id');
        if (!class_exists(\support\Redis::class)) {
            return;
        }
        foreach ($adminIds as $adminId) {
            try {
                \support\Redis::del("admin:permissions:{$adminId}", "admin:menus:{$adminId}");
            } catch (\Throwable) {
                break;
            }
        }
    }
}

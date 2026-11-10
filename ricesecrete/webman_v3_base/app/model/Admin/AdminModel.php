<?php

declare(strict_types=1);

namespace app\model\Admin;

use app\model\Concerns\DefinesTableSchema;
use app\model\Content\ArticleModel;
use app\model\Monitor\AdminLogModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminModel extends Model
{
    use DefinesTableSchema;
    use SoftDeletes;

    protected $table = 'admins';

    protected $fillable = ['username', 'password', 'nickname', 'avatar', 'status', 'remark', 'department_id'];

    protected $hidden = ['password'];

    public static function tableSchema(): array
    {
        return [
            'table' => 'admins',
            'comment' => '管理员表',
            'columns' => [
                'id' => ['type' => 'increments', 'comment' => '主键ID'],
                'username' => ['type' => 'string', 'length' => 50, 'comment' => '登录用户名'],
                'password' => ['type' => 'string', 'length' => 255, 'comment' => '登录密码（bcrypt）'],
                'nickname' => ['type' => 'string', 'length' => 50, 'default' => '', 'comment' => '昵称'],
                'avatar' => ['type' => 'string', 'length' => 255, 'default' => '', 'comment' => '头像地址'],
                'remark' => ['type' => 'string', 'length' => 255, 'default' => '', 'comment' => '备注'],
                'department_id' => ['type' => 'unsignedInteger', 'default' => 0, 'comment' => '部门ID，0表示未分配'],
                'status' => ['type' => 'tinyInteger', 'unsigned' => true, 'default' => 1, 'comment' => '状态：0禁用 1启用'],
                'deleted_at' => ['type' => 'timestamp', 'nullable' => true, 'comment' => '软删除时间'],
            ],
            'timestamps' => true,
            'columnComments' => [
                'created_at' => '创建时间',
                'updated_at' => '更新时间',
            ],
            'unique' => [
                ['columns' => ['username']],
            ],
            'indexes' => [
                ['columns' => ['status']],
                ['columns' => ['deleted_at']],
            ],
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleModel::class, 'admin_roles', 'admin_id', 'role_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(ArticleModel::class, 'author_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AdminLogModel::class, 'admin_id');
    }
}

<?php

declare(strict_types=1);

namespace app\model\Admin;

use app\model\Concerns\DefinesTableSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentModel extends Model
{
    use DefinesTableSchema;

    protected $table = 'departments';

    protected $fillable = ['name', 'parent_id', 'sort', 'status'];

    public static function tableSchema(): array
    {
        return [
            'table' => 'departments',
            'comment' => '部门表',
            'columns' => [
                'id' => ['type' => 'increments', 'comment' => '主键ID'],
                'name' => ['type' => 'string', 'length' => 100, 'comment' => '部门名称'],
                'parent_id' => ['type' => 'unsignedInteger', 'default' => 0, 'comment' => '父级部门ID'],
                'sort' => ['type' => 'unsignedInteger', 'default' => 0, 'comment' => '排序'],
                'status' => ['type' => 'tinyInteger', 'unsigned' => true, 'default' => 1, 'comment' => '状态：0禁用 1启用'],
            ],
            'timestamps' => true,
            'columnComments' => [
                'created_at' => '创建时间',
                'updated_at' => '更新时间',
            ],
            'indexes' => [
                ['columns' => ['parent_id']],
                ['columns' => ['status']],
            ],
        ];
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}

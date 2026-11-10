<?php

declare(strict_types=1);

namespace app\model\Content;

use app\model\Concerns\DefinesTableSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryModel extends Model
{
    use DefinesTableSchema;

    protected $table = 'categories';

    protected $fillable = ['name', 'slug', 'sort'];

    public static function tableSchema(): array
    {
        return [
            'table' => 'categories',
            'comment' => '文章分类表',
            'columns' => [
                'id' => ['type' => 'increments', 'comment' => '主键ID'],
                'name' => ['type' => 'string', 'length' => 50, 'comment' => '分类名称'],
                'slug' => ['type' => 'string', 'length' => 50, 'comment' => '分类标识'],
                'sort' => ['type' => 'integer', 'default' => 0, 'comment' => '排序值'],
            ],
            'timestamps' => true,
            'columnComments' => [
                'created_at' => '创建时间',
                'updated_at' => '更新时间',
            ],
            'unique' => [
                ['columns' => ['name']],
                ['columns' => ['slug']],
            ],
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(ArticleModel::class, 'category_id');
    }
}

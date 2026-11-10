<?php

declare(strict_types=1);

namespace app\model\Content;

use app\model\Concerns\DefinesTableSchema;
use app\model\Admin\AdminModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleModel extends Model
{
    use DefinesTableSchema;
    use SoftDeletes;

    protected $table = 'articles';

    protected $fillable = ['title', 'summary', 'content', 'category_id', 'status', 'author_id', 'view_count'];

    public static function tableSchema(): array
    {
        return [
            'table' => 'articles',
            'comment' => '文章表',
            'columns' => [
                'id' => ['type' => 'increments', 'comment' => '主键ID'],
                'title' => ['type' => 'string', 'length' => 200, 'comment' => '文章标题'],
                'summary' => ['type' => 'string', 'length' => 500, 'default' => '', 'comment' => '文章摘要'],
                'content' => ['type' => 'longText', 'nullable' => true, 'comment' => '文章内容'],
                'category_id' => ['type' => 'unsignedInteger', 'nullable' => true, 'comment' => '分类ID'],
                'status' => ['type' => 'tinyInteger', 'unsigned' => true, 'default' => 0, 'comment' => '状态：0草稿 1已发布'],
                'author_id' => ['type' => 'unsignedInteger', 'comment' => '作者管理员ID'],
                'view_count' => ['type' => 'unsignedInteger', 'default' => 0, 'comment' => '阅读量'],
                'deleted_at' => ['type' => 'timestamp', 'nullable' => true, 'comment' => '软删除时间'],
            ],
            'timestamps' => true,
            'columnComments' => [
                'created_at' => '创建时间',
                'updated_at' => '更新时间',
            ],
            'indexes' => [
                ['columns' => ['status']],
                ['columns' => ['category_id']],
                ['columns' => ['author_id']],
                ['columns' => ['created_at']],
                ['columns' => ['deleted_at']],
            ],
            'foreignKeys' => [
                ['column' => 'author_id', 'references' => 'admins', 'on' => 'id'],
                ['column' => 'category_id', 'references' => 'categories', 'on' => 'id', 'nullOnDelete' => true],
            ],
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(AdminModel::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }
}

<?php

declare(strict_types=1);

namespace app\service;

use app\exception\BusinessException;
use app\model\Content\ArticleModel;
use app\model\Content\CategoryModel;
use app\support\ErrorCode;

class CategoryService
{
    public function list(): array
    {
        return CategoryModel::orderBy('sort')->get()->toArray();
    }

    public function create(array $data): CategoryModel
    {
        if (CategoryModel::where('name', $data['name'])->orWhere('slug', $data['slug'])->exists()) {
            throw new BusinessException(ErrorCode::VALIDATION_FAILED, '分类名称或标识已存在');
        }
        return CategoryModel::create($data);
    }

    public function update(int $id, array $data): CategoryModel
    {
        $category = CategoryModel::find($id);
        if (!$category) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '分类不存在');
        }
        $category->update($data);
        return $category;
    }

    public function delete(int $id): void
    {
        $category = CategoryModel::find($id);
        if (!$category) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '分类不存在');
        }
        if (ArticleModel::where('category_id', $id)->exists()) {
            throw new BusinessException(ErrorCode::VALIDATION_FAILED, '分类下存在文章，无法删除');
        }
        $category->delete();
    }
}

<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\controller\concerns\DefinesAdminMenu;
use support\Request;
use app\service\CategoryService;

class CategoryController extends BaseController
{
    use DefinesAdminMenu;

public static function menuConfig(): ?array
    {
        return [
            'group' => [
                'name' => '内容管理',
                'slug' => 'content',
                'path' => '/content',
                'icon' => 'document',
                'sort' => 10,
            ],
            'menu' => [
                'name' => '分类管理',
                'slug' => 'category-menu',
                'path' => '/content/category',
                'icon' => 'folder',
                'component' => 'views/category/index',
                'sort' => 2,
            ],
            'apis' => [
                ['name' => '分类列表', 'slug' => 'category:list', 'path' => '/admin/categories', 'method' => 'GET', 'sort' => 1],
                ['name' => '创建分类', 'slug' => 'category:create', 'path' => '/admin/categories', 'method' => 'POST', 'sort' => 2],
                ['name' => '更新分类', 'slug' => 'category:update', 'path' => '/admin/categories/*', 'method' => 'PUT', 'sort' => 3],
                ['name' => '删除分类', 'slug' => 'category:delete', 'path' => '/admin/categories/*', 'method' => 'DELETE', 'sort' => 4],
            ],
        ];
    }

    protected CategoryService $categoryService;

    public function __construct(?CategoryService $categoryService = null)
    {
        $this->categoryService = $categoryService ?? new CategoryService();
    }

    public function index(Request $request)
    {
        return success($this->categoryService->list());
    }

    public function store(Request $request)
    {
        $data = $request->only(['name', 'slug', 'sort']);
        if (empty($data['name']) || empty($data['slug'])) {
            return fail(42201, '名称和标识不能为空');
        }
        $data['sort'] = (int) ($data['sort'] ?? 0);
        return success($this->categoryService->create($data), '创建成功');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->only(['name', 'slug', 'sort']);
        return success($this->categoryService->update($id, $data), '更新成功');
    }

    public function destroy(Request $request, int $id)
    {
        $this->categoryService->delete($id);
        return success(null, '删除成功');
    }
}

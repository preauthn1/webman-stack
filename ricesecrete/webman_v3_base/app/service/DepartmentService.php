<?php

declare(strict_types=1);

namespace app\service;

use app\exception\BusinessException;
use app\model\Admin\DepartmentModel;
use app\support\ErrorCode;

class DepartmentService
{
    public function tree(): array
    {
        $items = DepartmentModel::query()
            ->where('status', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->toArray();

        return $this->buildTree($items);
    }

    public function create(array $data): DepartmentModel
    {
        return DepartmentModel::create($data);
    }

    public function update(int $id, array $data): DepartmentModel
    {
        $department = DepartmentModel::find($id);
        if (!$department) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '部门不存在');
        }
        $department->update($data);
        return $department;
    }

    public function delete(int $id): void
    {
        $department = DepartmentModel::find($id);
        if (!$department) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '部门不存在');
        }
        if (DepartmentModel::where('parent_id', $id)->exists()) {
            throw new BusinessException(ErrorCode::VALIDATION_FAILED, '存在子部门，无法删除');
        }
        $department->delete();
    }

    private function buildTree(array $items, int $parentId = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ((int) $item['parent_id'] === $parentId) {
                $node = $item;
                $children = $this->buildTree($items, (int) $item['id']);
                if ($children !== []) {
                    $node['children'] = $children;
                }
                $tree[] = $node;
            }
        }
        return $tree;
    }
}

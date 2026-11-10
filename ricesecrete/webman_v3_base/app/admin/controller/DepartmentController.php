<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\service\DepartmentService;
use support\Request;

class DepartmentController extends BaseController
{
    protected DepartmentService $service;

    public function __construct(?DepartmentService $service = null)
    {
        $this->service = $service ?? new DepartmentService();
    }

    public function tree(Request $request)
    {
        return success($this->service->tree());
    }

    public function store(Request $request)
    {
        $data = $request->only(['name', 'parent_id', 'sort', 'status']);
        return success($this->service->create($data), '创建成功');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->only(['name', 'parent_id', 'sort', 'status']);
        return success($this->service->update($id, $data), '更新成功');
    }

    public function destroy(Request $request, int $id)
    {
        $this->service->delete($id);
        return success(null, '删除成功');
    }
}

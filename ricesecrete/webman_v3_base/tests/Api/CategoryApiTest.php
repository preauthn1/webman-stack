<?php

declare(strict_types=1);

namespace tests\Api;

use tests\ApiTestCase;

class CategoryApiTest extends ApiTestCase
{
    public function testListCategories(): void
    {
        $response = $this->get('/admin/categories');
        $this->assertEquals(0, $response['code']);
        $this->assertNotEmpty($response['data']);
    }

    public function testCreateAndDeleteCategory(): void
    {
        $slug = 'test_' . time();
        $create = $this->post('/admin/categories', [
            'name' => '测试分类',
            'slug' => $slug,
            'sort' => 99,
        ], true);
        $this->assertEquals(0, $create['code']);
        $id = $create['data']['id'];

        $delete = $this->delete('/admin/categories/' . $id);
        $this->assertEquals(0, $delete['code']);
    }
}

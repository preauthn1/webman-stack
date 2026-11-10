<?php

declare(strict_types=1);

namespace tests\Api;

use tests\ApiTestCase;

class ArticleApiTest extends ApiTestCase
{
    private ?int $articleId = null;

    public function testListArticles(): void
    {
        $response = $this->get('/admin/articles', ['page' => 1, 'limit' => 10]);
        $this->assertEquals(0, $response['code']);
        $this->assertArrayHasKey('total', $response['data']);
        $this->assertArrayHasKey('items', $response['data']);
    }

    public function testCreateAndDeleteArticle(): void
    {
        $create = $this->post('/admin/articles', [
            'title' => 'PHPUnit 测试文章',
            'content' => '<p>测试内容</p>',
            'summary' => '测试摘要',
            'status' => 1,
        ], true);
        $this->assertEquals(0, $create['code']);
        $this->articleId = $create['data']['id'];

        $detail = $this->get('/admin/articles/' . $this->articleId);
        $this->assertEquals(0, $detail['code']);
        $this->assertEquals('PHPUnit 测试文章', $detail['data']['title']);

        $update = $this->put('/admin/articles/' . $this->articleId, [
            'title' => 'PHPUnit 更新文章',
        ]);
        $this->assertEquals(0, $update['code']);

        $delete = $this->delete('/admin/articles/' . $this->articleId);
        $this->assertEquals(0, $delete['code']);
        $this->articleId = null;
    }

    public function testPublicArticleList(): void
    {
        $this->token = null;
        $response = $this->get('/api/articles');
        $this->assertEquals(0, $response['code']);
        $this->assertArrayHasKey('items', $response['data']);
    }

    protected function tearDown(): void
    {
        if ($this->articleId) {
            $this->delete('/admin/articles/' . $this->articleId);
        }
    }
}

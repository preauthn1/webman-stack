<?php

declare(strict_types=1);

namespace tests\Api;

use tests\ApiTestCase;

class SettingApiTest extends ApiTestCase
{
    public function testListSettings(): void
    {
        $response = $this->get('/admin/settings');
        $this->assertEquals(0, $response['code']);
        $this->assertNotEmpty($response['data']);
    }

    public function testGetAndUpdateSetting(): void
    {
        $get = $this->get('/admin/settings/site_name');
        $this->assertEquals(0, $get['code']);

        $update = $this->put('/admin/settings/site_name', ['value' => 'Webman Admin Test']);
        $this->assertEquals(0, $update['code']);

        // 恢复
        $this->put('/admin/settings/site_name', ['value' => 'Webman Admin']);
    }
}

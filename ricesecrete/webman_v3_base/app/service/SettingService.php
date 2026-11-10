<?php

declare(strict_types=1);

namespace app\service;

use app\exception\BusinessException;
use app\support\ErrorCode;
use app\model\System\SettingModel;
use support\Redis;

class SettingService
{
    public function list(): array
    {
        return SettingModel::orderBy('id')->get()->toArray();
    }

    public function get(string $key): SettingModel
    {
        $setting = SettingModel::where('key', $key)->first();
        if (!$setting) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '配置不存在');
        }
        return $setting;
    }

    public function getValue(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting:{$key}";
        $cached = Redis::get($cacheKey);
        if ($cached !== false && $cached !== null) {
            return json_decode($cached, true);
        }

        $setting = SettingModel::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        $value = $setting->value;
        Redis::setex($cacheKey, 86400, json_encode($value, JSON_UNESCAPED_UNICODE));

        return $value;
    }

    /**
     * @return array{
     *     credentials_key: string,
     *     credentials_secret: string,
     *     region: string,
     *     bucket: string,
     *     url: string,
     *     proxy: string|null,
     *     presign_expires: int
     * }
     */
    public function getS3Config(): array
    {
        $config = $this->getValue('s3_config', []);
        if (!is_array($config)) {
            $config = [];
        }

        return [
            'credentials_key' => (string) ($config['credentials_key'] ?? ''),
            'credentials_secret' => (string) ($config['credentials_secret'] ?? ''),
            'region' => (string) ($config['region'] ?? 'ap-east-1'),
            'bucket' => (string) ($config['bucket'] ?? ''),
            'url' => rtrim((string) ($config['url'] ?? ''), '/'),
            'proxy' => isset($config['proxy']) && $config['proxy'] !== '' ? (string) $config['proxy'] : null,
            'presign_expires' => max(60, (int) ($config['presign_expires'] ?? 900)),
        ];
    }

    public function update(string $key, mixed $value): SettingModel
    {
        $setting = SettingModel::where('key', $key)->first();
        if (!$setting) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '配置不存在');
        }
        $setting->update(['value' => $value]);
        Redis::del("setting:{$key}");
        return $setting;
    }

    public function batchUpdate(array $settings): void
    {
        foreach ($settings as $item) {
            $this->update($item['key'], $item['value']);
        }
    }
}

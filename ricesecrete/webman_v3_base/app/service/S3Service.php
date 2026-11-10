<?php

declare(strict_types=1);

namespace app\service;

use app\exception\BusinessException;
use app\support\ErrorCode;
use Aws\S3\S3Client;
use Webman\Http\UploadFile;

class S3Service
{
    private const TYPE_RULES = [
        'image' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'max_size' => 'UPLOAD_MAX_SIZE',
            'default_max_size' => 5242880,
            'prefix' => 'images',
        ],
        'document' => [
            'extensions' => ['pdf', 'doc', 'docx'],
            'mimes' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            'max_size' => 'UPLOAD_DOCUMENT_MAX_SIZE',
            'default_max_size' => 10485760,
            'prefix' => 'documents',
        ],
    ];

    private ?S3Client $client = null;

    protected SettingService $settingService;

    public function __construct(?SettingService $settingService = null)
    {
        $this->settingService = $settingService ?? new SettingService();
    }

    public function createPresignedUpload(string $filename, string $mimeType, string $type): array
    {
        $extension = $this->validateMeta($filename, $mimeType, 0, $type, false);
        $key = $this->generateObjectKey($type, $extension);
        $config = $this->settingService->getS3Config();

        $command = $this->client()->getCommand('PutObject', [
            'Bucket' => $this->bucket(),
            'Key' => $key,
            'ContentType' => $mimeType,
        ]);

        $expires = $config['presign_expires'];
        $request = $this->client()->createPresignedRequest($command, "+{$expires} seconds");

        return [
            'upload_url' => (string) $request->getUri(),
            'key' => $key,
            'url' => $this->publicUrl($key),
            'expires_in' => $expires,
        ];
    }

    public function upload(UploadFile $file, string $type): array
    {
        if (!$file->isValid()) {
            throw new BusinessException(ErrorCode::VALIDATION_FAILED, '文件上传失败');
        }

        $filename = (string) $file->getUploadName();
        $mimeType = (string) $file->getUploadMimeType();
        $size = (int) $file->getSize();

        $extension = $this->validateMeta($filename, $mimeType, $size, $type, true);
        $key = $this->generateObjectKey($type, $extension);

        $this->client()->putObject([
            'Bucket' => $this->bucket(),
            'Key' => $key,
            'SourceFile' => $file->getPathname(),
            'ContentType' => $mimeType,
        ]);

        return [
            'url' => $this->publicUrl($key),
            'key' => $key,
            'filename' => $filename,
            'size' => $size,
        ];
    }

    private function validateMeta(
        string $filename,
        string $mimeType,
        int $size,
        string $type,
        bool $checkSize
    ): string {
        if (!isset(self::TYPE_RULES[$type])) {
            throw new BusinessException(ErrorCode::VALIDATION_FAILED, '不支持的文件类型参数');
        }

        $rules = self::TYPE_RULES[$type];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($extension === '' || !in_array($extension, $rules['extensions'], true)) {
            throw new BusinessException(ErrorCode::VALIDATION_FAILED, '不支持的文件扩展名');
        }

        if ($mimeType === '' || !in_array($mimeType, $rules['mimes'], true)) {
            throw new BusinessException(ErrorCode::VALIDATION_FAILED, '不支持的文件 MIME 类型');
        }

        if ($checkSize) {
            $maxSize = (int) env($rules['max_size'], $rules['default_max_size']);
            if ($size <= 0 || $size > $maxSize) {
                throw new BusinessException(ErrorCode::VALIDATION_FAILED, '文件大小超出限制');
            }
        }

        return $extension;
    }

    private function generateObjectKey(string $type, string $extension): string
    {
        $prefix = self::TYPE_RULES[$type]['prefix'];
        $date = date('Y/m');
        $name = bin2hex(random_bytes(16));

        return "{$prefix}/{$date}/{$name}.{$extension}";
    }

    private function publicUrl(string $key): string
    {
        return $this->baseUrl() . '/' . ltrim($key, '/');
    }

    private function baseUrl(): string
    {
        $config = $this->settingService->getS3Config();
        if ($config['url'] !== '') {
            return rtrim($config['url'], '/');
        }

        $bucket = $this->bucket();
        $region = $config['region'];

        return "https://{$bucket}.s3.{$region}.amazonaws.com";
    }

    private function bucket(): string
    {
        $bucket = $this->settingService->getS3Config()['bucket'];
        if ($bucket === '') {
            throw new BusinessException(ErrorCode::INTERNAL_ERROR, 'S3 存储桶未配置');
        }

        return $bucket;
    }

    private function client(): S3Client
    {
        if ($this->client !== null) {
            return $this->client;
        }

        $config = $this->settingService->getS3Config();
        if ($config['credentials_key'] === '' || $config['credentials_secret'] === '') {
            throw new BusinessException(ErrorCode::INTERNAL_ERROR, 'S3 访问凭证未配置');
        }

        $options = [
            'version' => 'latest',
            'region' => $config['region'],
            'credentials' => [
                'key' => $config['credentials_key'],
                'secret' => $config['credentials_secret'],
            ],
        ];

        if (!empty($config['proxy'])) {
            $options['http'] = ['proxy' => $config['proxy']];
        }

        $this->client = new S3Client($options);

        return $this->client;
    }
}

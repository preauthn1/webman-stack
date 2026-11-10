<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\controller\concerns\DefinesAdminMenu;
use app\service\S3Service;
use support\Request;

class UploadController extends BaseController
{
    use DefinesAdminMenu;

    protected S3Service $s3Service;

    public function __construct(?S3Service $s3Service = null)
    {
        $this->s3Service = $s3Service ?? new S3Service();
    }

    public function presign(Request $request)
    {
        $filename = trim((string) $request->post('filename', ''));
        $mimeType = trim((string) $request->post('mime_type', ''));
        $type = trim((string) $request->post('type', 'image'));

        if ($filename === '') {
            return fail(42201, '文件名不能为空');
        }
        if ($mimeType === '') {
            return fail(42201, 'MIME 类型不能为空');
        }
        if (!in_array($type, ['image', 'document'], true)) {
            return fail(42201, 'type 必须是 image 或 document');
        }

        return success($this->s3Service->createPresignedUpload($filename, $mimeType, $type));
    }
}

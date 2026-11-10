<?php

declare(strict_types=1);

use support\Response;
use app\support\ErrorCode;

function json_response(array $data, int $status = 200)
{
    return new Response(
        $status,
        ['Content-Type' => 'application/json'],
        json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
    );
}

function success(mixed $data = null, string $msg = 'success')
{
    return json_response(['code' => 0, 'msg' => $msg, 'data' => $data]);
}

function fail(int $code, ?string $msg = null, int $httpStatus = 200)
{
    return json_response(['code' => $code, 'msg' => ErrorCode::message($code, $msg), 'data' => null], $httpStatus);
}

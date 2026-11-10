# 本地开发环境配置

> **注意：** 此文件包含本地开发凭据，请勿提交到版本库。  
> 正式配置请使用 `.env` 文件，参考 `.env.example` 模板。

## MySQL

```
主机: 127.0.0.1
端口: 3306
用户名: root
密码: 123456
数据库: webman_admin
```

对应 `.env` 配置：

```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webman_admin
DB_USERNAME=root
DB_PASSWORD=123456
```

## Redis

```
主机: 127.0.0.1
端口: 6379
密码: (无)
```

对应 `.env` 配置：

```ini
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DATABASE=0
```

## 初始化数据库

```bash
# 创建数据库
mysql -u root -p123456 -e "CREATE DATABASE IF NOT EXISTS webman_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 执行迁移
php webman migrate:run

# 填充初始数据
php webman seed:run
```

## 默认登录账号

| 用户名 | 密码 | 角色 |
|--------|------|------|
| admin | admin123 | 超级管理员 |

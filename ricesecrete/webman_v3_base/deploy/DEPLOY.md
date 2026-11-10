# 生产环境部署指南

## 一、服务器要求

- Linux (Ubuntu 22.04+ / CentOS 8+)
- PHP 8.1+ (扩展: pdo_mysql, redis, pcntl, mbstring, json)
- MySQL 8.0+
- Redis 7.0+
- Nginx 1.20+
- Node.js 18+ (构建前端)

## 二、部署步骤

### 1. 克隆代码

```bash
git clone <your-repo> /var/www/webman-admin
cd /var/www/webman-admin
```

### 2. 后端

```bash
composer install --no-dev --optimize-autoloader

cp .env.production.example .env
# 编辑 .env 填入生产配置

php database/migrate.php
php database/seed.php

# 启动（推荐使用 systemd 管理）
php start.php start -d
```

### 3. 前端

```bash
cd frontend
pnpm install
cp .env.pro .env.production.local
# 设置 VITE_API_BASE_PATH= 留空（同域部署）或 https://api.example.com

pnpm run build:pro
# 产物在 frontend/dist/
```

### 4. Nginx

```bash
cp deploy/nginx/webman-admin.conf /etc/nginx/sites-available/webman-admin.conf
ln -s /etc/nginx/sites-available/webman-admin.conf /etc/nginx/sites-enabled/
# 修改 server_name 和路径

nginx -t
systemctl reload nginx
```

### 5. Systemd 服务（可选）

```ini
# /etc/systemd/system/webman-admin.service
[Unit]
Description=Webman Admin
After=network.target mysql.service redis.service

[Service]
Type=forking
User=www-data
WorkingDirectory=/var/www/webman-admin
ExecStart=/usr/bin/php start.php start -d
ExecReload=/usr/bin/php start.php reload
ExecStop=/usr/bin/php start.php stop
Restart=always

[Install]
WantedBy=multi-user.target
```

```bash
systemctl enable webman-admin
systemctl start webman-admin
```

## 三、Docker 部署

```bash
docker compose up -d
```

详见项目根目录 `docker-compose.yml`。

## 四、安全检查清单

- [ ] 修改默认管理员密码
- [ ] 更换 JWT_SECRET 为随机字符串
- [ ] APP_DEBUG 设为 false
- [ ] 数据库使用独立账号（非 root）
- [ ] 配置 HTTPS
- [ ] 限制 Redis 访问
- [ ] 配置防火墙（仅开放 80/443）

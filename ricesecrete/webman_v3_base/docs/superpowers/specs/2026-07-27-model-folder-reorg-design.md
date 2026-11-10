# Model 按业务域分文件夹设计

**日期:** 2026-07-27  
**状态:** 已批准

## 目标

将 `app/model` 下平铺的 Eloquent 模型按业务域分子目录，文件夹与 PSR-4 命名空间一一对应，并同步更新全项目引用。

## 决策

| 项 | 选择 |
|----|------|
| 命名空间 | 文件夹对应命名空间（如 `app\model\Admin\AdminModel`） |
| 模块划分 | 按业务域：Admin / Content / System / Monitor |
| 类名 | 保持 `XxxModel`，不重命名 |
| Service 目录 | 本次不调整 |

## 目标结构

```
app/model/
├── Admin/
│   ├── AdminModel.php
│   ├── AdminRoleModel.php
│   ├── RoleModel.php
│   ├── PermissionModel.php
│   ├── RolePermissionModel.php
│   └── DepartmentModel.php
├── Content/
│   ├── ArticleModel.php
│   └── CategoryModel.php
├── System/
│   ├── SettingModel.php
│   ├── DictTypeModel.php
│   └── DictItemModel.php
├── Monitor/
│   ├── AdminLogModel.php
│   ├── AdminIpWhitelistModel.php
│   └── AdminNotificationModel.php
└── Concerns/
    └── DefinesTableSchema.php   # namespace 仍为 app\model\Concerns
```

## 变更范围

1. 移动模型文件并修改 `namespace`
2. 跨模块关联补充 `use`（同模块内可省略）
3. 更新 Service / Controller / 文档中的 `use app\model\...`
4. `ModelSchemaSynchronizer` 改为递归扫描子目录并解析 FQCN
5. `ModelCommand --make` 支持 `Module/ClassName`（可选模块路径）
6. `Concerns` 保持原命名空间与路径

## 非目标

- 不重命名类、不改表结构
- 不调整 Service / Controller 目录结构
- 不引入 class alias / 兼容旧命名空间

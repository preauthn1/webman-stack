# Model Folder Reorg Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 按业务域将 `app/model` 模型分文件夹并同步命名空间与引用。

**Architecture:** PSR-4 子命名空间 `Admin` / `Content` / `System` / `Monitor`；类名不变；递归发现模型以支持 `php webman model`。

**Tech Stack:** PHP 8.1+ / Webman / Eloquent

## Global Constraints

- 类名保持 `XxxModel`
- `Concerns\DefinesTableSchema` 命名空间不变
- 不改数据库表结构

---

### Task 1: 移动模型并改 namespace

- [x] 创建 Admin/Content/System/Monitor 目录
- [x] `git mv` 各模型到对应目录
- [x] 更新各文件 `namespace` 与跨模块 `use`

### Task 2: 更新消费者引用

- [x] 更新所有 Service / Controller / DEVELOPMENT.md 中的 `use`
- [x] 更新 ARCHITECTURE.md 目录说明

### Task 3: 修复 schema 发现与 make

- [x] `ModelSchemaSynchronizer::discover` 递归扫描
- [x] `ModelCommand::makeModel` 支持 `Module/Name`

### Task 4: 验证

- [x] `composer dump-autoload`（如需要）
- [x] 确认无残留旧 `use app\model\XxxModel`（除文档历史外）

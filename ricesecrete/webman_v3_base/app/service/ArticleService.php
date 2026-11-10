<?php

declare(strict_types=1);

namespace app\service;

use app\exception\BusinessException;
use app\model\Content\ArticleModel;
use app\support\ErrorCode;

class ArticleService
{
    protected PermissionService $permissionService;

    public function __construct(?PermissionService $permissionService = null)
    {
        $this->permissionService = $permissionService ?? new PermissionService();
    }

    public function list(int $page, int $limit, array $filters = [], bool $publicOnly = false, ?int $adminId = null): array
    {
        $query = ArticleModel::query();

        if ($publicOnly) {
            $query->where('status', 1);
        } elseif (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['keyword'])) {
            $query->where('title', 'like', '%' . $filters['keyword'] . '%');
        }

        if (!$publicOnly && $adminId !== null) {
            $this->permissionService->applyDataScope($query, $adminId, 'author_id');
        }

        $total = $query->count();
        $items = $query->with(['author:id,nickname', 'category:id,name'])
            ->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return ['total' => $total, 'items' => $items];
    }

    public function find(int $id, bool $publicOnly = false, ?int $adminId = null): ArticleModel
    {
        $article = ArticleModel::with(['author:id,nickname', 'category:id,name'])->find($id);
        if (!$article) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '文章不存在');
        }
        if ($publicOnly && $article->status !== 1) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '文章不存在');
        }
        if (!$publicOnly && $adminId !== null) {
            $this->assertCanAccess($adminId, (int) $article->author_id);
        }
        return $article;
    }

    public function create(array $data, int $authorId): ArticleModel
    {
        $data['author_id'] = $authorId;
        return ArticleModel::create($data);
    }

    public function update(int $id, array $data, ?int $adminId = null): ArticleModel
    {
        $article = ArticleModel::find($id);
        if (!$article) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '文章不存在');
        }
        if ($adminId !== null) {
            $this->assertCanAccess($adminId, (int) $article->author_id);
        }
        $article->update($data);
        return $article->fresh(['author:id,nickname', 'category:id,name']);
    }

    public function delete(int $id, ?int $adminId = null): void
    {
        $article = ArticleModel::find($id);
        if (!$article) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '文章不存在');
        }
        if ($adminId !== null) {
            $this->assertCanAccess($adminId, (int) $article->author_id);
        }
        $article->delete();
    }

    public function incrementView(int $id): void
    {
        ArticleModel::where('id', $id)->increment('view_count');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(?int $adminId = null): array
    {
        $query = ArticleModel::query()->with(['author:id,nickname', 'category:id,name']);
        if ($adminId !== null) {
            $this->permissionService->applyDataScope($query, $adminId, 'author_id');
        }

        return $query->orderBy('id')->get()->map(static function (ArticleModel $article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'summary' => $article->summary,
                'category' => $article->category?->name ?? '',
                'author' => $article->author?->nickname ?? '',
                'status' => $article->status,
                'view_count' => $article->view_count,
                'created_at' => (string) $article->created_at,
            ];
        })->all();
    }

    public function importRows(array $rows, int $authorId): int
    {
        $imported = 0;
        foreach ($rows as $row) {
            if (empty($row['title'])) {
                continue;
            }
            ArticleModel::create([
                'title' => (string) $row['title'],
                'summary' => (string) ($row['summary'] ?? ''),
                'content' => (string) ($row['content'] ?? (string) ($row['summary'] ?? '')),
                'category_id' => (int) ($row['category_id'] ?? 1),
                'status' => (int) ($row['status'] ?? 0),
                'author_id' => $authorId,
            ]);
            $imported++;
        }

        return $imported;
    }

    private function assertCanAccess(int $adminId, int $ownerId): void
    {
        if (!$this->permissionService->canAccessOwnedRecord($adminId, $ownerId)) {
            throw new BusinessException(ErrorCode::FORBIDDEN, '无权访问该数据');
        }
    }
}

<?php

declare(strict_types=1);

namespace app\api\controller;

use support\Request;
use app\service\ArticleService;

class ArticleController
{
    protected ArticleService $articleService;

    public function __construct(?ArticleService $articleService = null)
    {
        $this->articleService = $articleService ?? new ArticleService();
    }

    public function index(Request $request)
    {
        $page = max(1, (int) $request->get('page', 1));
        $limit = min(100, max(1, (int) $request->get('limit', 10)));
        $filters = $request->only(['keyword', 'category_id']);

        return success($this->articleService->list($page, $limit, $filters, true));
    }

    public function show(Request $request, int $id)
    {
        $article = $this->articleService->find($id, true);
        $this->articleService->incrementView($id);
        return success($article);
    }
}

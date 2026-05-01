<?php
// app/Http/Controllers/Web/BlogController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class BlogController extends Controller
{
    /**
     * ブログのトップページを表示する。
     */
    public function index(): Response
    {
        return response()->view('home');
    }

    /**
     * 新着記事一覧ページを表示する。
     */
    public function recent(): Response
    {
        $posts = $this->basePostFeedQuery()
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $postIds = $posts->pluck('id')->all();
        $postReactionSummaries = [];

        if ($postIds !== []) {
            $rawPostReactionRows = DB::table('post_reactions')
                ->whereIn('post_id', $postIds)
                ->select('post_id', 'reaction', DB::raw('COUNT(*) as total'))
                ->groupBy('post_id', 'reaction')
                ->get();

            foreach ($rawPostReactionRows as $row) {
                $postId = (int) $row->post_id;

                if (! isset($postReactionSummaries[$postId])) {
                    $postReactionSummaries[$postId] = [];
                }

                $postReactionSummaries[$postId][$row->reaction] = (int) $row->total;
            }
        }

        $currentUserPostReactions = [];

        if (auth()->check() && $postIds !== []) {
            $currentUserPostReactions = auth()->user()
                ->postReactions()
                ->whereIn('post_id', $postIds)
                ->pluck('reaction', 'post_id')
                ->toArray();
        }

        return response()->view('posts.recent', [
            'posts' => $posts,
            'postReactionSummaries' => $postReactionSummaries,
            'currentUserPostReactions' => $currentUserPostReactions,
            'reactionOptions' => config('reactions.options', []),
        ]);
    }

    /**
     * 人気記事一覧ページを表示する。
     */
    public function popular(): Response
    {
        $commentsCountSubquery = '(select count(*) from comments where posts.id = comments.post_id and comments.deleted_at is null)';
        $popularityScoreExpression = '(COALESCE(post_reaction_stats.reaction_score, 0) * 1.5) + (' . $commentsCountSubquery . ' * 2) + (posts.views_count * 0.2)';

        $posts = $this->basePostFeedQuery()
            ->selectRaw("{$popularityScoreExpression} as popularity_score")
            ->orderByRaw("{$popularityScoreExpression} DESC")
            ->orderByDesc('posts.created_at')
            ->paginate(8)
            ->withQueryString();

        $postIds = $posts->pluck('id')->all();
        $postReactionSummaries = [];

        if ($postIds !== []) {
            $rawPostReactionRows = DB::table('post_reactions')
                ->whereIn('post_id', $postIds)
                ->select('post_id', 'reaction', DB::raw('COUNT(*) as total'))
                ->groupBy('post_id', 'reaction')
                ->get();

            foreach ($rawPostReactionRows as $row) {
                $postId = (int) $row->post_id;

                if (! isset($postReactionSummaries[$postId])) {
                    $postReactionSummaries[$postId] = [];
                }

                $postReactionSummaries[$postId][$row->reaction] = (int) $row->total;
            }
        }

        $currentUserPostReactions = [];

        if (auth()->check() && $postIds !== []) {
            $currentUserPostReactions = auth()->user()
                ->postReactions()
                ->whereIn('post_id', $postIds)
                ->pluck('reaction', 'post_id')
                ->toArray();
        }

        return response()->view('posts.popular', [
            'posts' => $posts,
            'postReactionSummaries' => $postReactionSummaries,
            'currentUserPostReactions' => $currentUserPostReactions,
            'reactionOptions' => config('reactions.options', []),
        ]);
    }

    /**
     * 記事詳細ページを表示する。
     */
    public function show(Post $post): Response
    {
        $viewerKey = auth()->id() ? 'user:' . auth()->id() : 'ip:' . (request()->ip() ?? 'unknown');
        $cacheKey = 'post:view:' . $post->id . ':' . $viewerKey;
        $commentSort = request()->string('comment_sort')->toString() === 'popular' ? 'popular' : 'new';

        if (Cache::add($cacheKey, true, now()->addMinutes(30))) {
            $post->increment('views_count');
        }

        $post->refresh();

        $post->loadCount(['comments']);

        $postReactionsSummary = DB::table('post_reactions')
            ->where('post_id', $post->id)
            ->select('reaction', DB::raw('COUNT(*) as total'))
            ->groupBy('reaction')
            ->pluck('total', 'reaction')
            ->all();

        $postReactionsCount = array_sum($postReactionsSummary);
        $currentUserPostReaction = null;

        if (auth()->check()) {
            $currentUserPostReaction = DB::table('post_reactions')
                ->where('post_id', $post->id)
                ->where('user_id', auth()->id())
                ->value('reaction');
        }

        $commentReactionStatsSubQuery = DB::table('comment_reactions')
            ->select('comment_id')
            ->selectRaw('COUNT(*) as reactions_count')
            ->selectRaw('SUM(' . $this->reactionWeightCaseExpression('comment_reactions.reaction') . ') as reaction_score')
            ->groupBy('comment_id');

        $comments = Comment::query()
            ->where('post_id', $post->id)
            ->with('user:id,name')
            ->leftJoinSub($commentReactionStatsSubQuery, 'comment_reaction_stats', function ($join): void {
                $join->on('comments.id', '=', 'comment_reaction_stats.comment_id');
            })
            ->select('comments.*')
            ->selectRaw('COALESCE(comment_reaction_stats.reactions_count, 0) as reactions_count')
            ->selectRaw('COALESCE(comment_reaction_stats.reaction_score, 0) as reaction_score')
            ->withCount('replies')
            ->when($commentSort === 'popular', function ($query): void {
                $query
                    ->orderByRaw('((reaction_score * 1.5) + (replies_count * 2)) DESC')
                    ->orderByDesc('comments.created_at');
            }, function ($query): void {
                $query->orderByDesc('comments.created_at');
            })
            ->get();

        $commentsByParentId = $comments->groupBy(function (Comment $comment): int {
            return $comment->parent_id ?? 0;
        });

        $commentReactionSummaries = [];
        $commentIds = $comments->pluck('id')->all();

        if ($commentIds !== []) {
            $rawCommentReactionRows = DB::table('comment_reactions')
                ->whereIn('comment_id', $commentIds)
                ->select('comment_id', 'reaction', DB::raw('COUNT(*) as total'))
                ->groupBy('comment_id', 'reaction')
                ->get();

            foreach ($rawCommentReactionRows as $row) {
                $commentId = (int) $row->comment_id;

                if (! isset($commentReactionSummaries[$commentId])) {
                    $commentReactionSummaries[$commentId] = [];
                }

                $commentReactionSummaries[$commentId][$row->reaction] = (int) $row->total;
            }
        }

        $currentUserCommentReactions = [];

        if (auth()->check()) {
            $user = auth()->user();

            if ($commentIds !== []) {
                $currentUserCommentReactions = $user->commentReactions()
                    ->whereIn('comment_id', $commentIds)
                    ->pluck('reaction', 'comment_id')
                    ->toArray();
            }
        }

        return response()->view('posts.show', [
            'post' => $post,
            'commentsByParentId' => $commentsByParentId,
            'postReactionsSummary' => $postReactionsSummary,
            'postReactionsCount' => $postReactionsCount,
            'currentUserPostReaction' => $currentUserPostReaction,
            'reactionOptions' => config('reactions.options', []),
            'commentReactionSummaries' => $commentReactionSummaries,
            'currentUserCommentReactions' => $currentUserCommentReactions,
            'commentSort' => $commentSort,
        ]);
    }

    /**
     * フィード系の投稿一覧クエリを返す。
     */
    private function basePostFeedQuery(): Builder
    {
        $postReactionStatsSubQuery = DB::table('post_reactions')
            ->select('post_id')
            ->selectRaw('COUNT(*) as reactions_count')
            ->selectRaw('SUM(' . $this->reactionWeightCaseExpression('post_reactions.reaction') . ') as reaction_score')
            ->groupBy('post_id');

        return Post::query()
            ->leftJoinSub($postReactionStatsSubQuery, 'post_reaction_stats', function ($join): void {
                $join->on('posts.id', '=', 'post_reaction_stats.post_id');
            })
            ->select('posts.*')
            ->withCount('comments')
            ->selectRaw('COALESCE(post_reaction_stats.reactions_count, 0) as reactions_count')
            ->selectRaw('COALESCE(post_reaction_stats.reaction_score, 0) as reaction_score');
    }

    /**
     * リアクション種別を重みへ変換する CASE 式を返す。
     */
    private function reactionWeightCaseExpression(string $reactionColumn): string
    {
        $options = config('reactions.options', []);
        $cases = [];

        foreach ($options as $reaction => $meta) {
            $weight = (int) ($meta['weight'] ?? 0);
            $cases[] = "WHEN '{$reaction}' THEN {$weight}";
        }

        return 'CASE ' . $reactionColumn . ' ' . implode(' ', $cases) . ' ELSE 0 END';
    }
}

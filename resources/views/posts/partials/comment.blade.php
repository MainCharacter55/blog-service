@php
    $childComments = $commentsByParentId->get($comment->id, collect());
    $isOwnComment = auth()->check() && auth()->id() === $comment->user_id;
    $canManageComment = auth()->check() && ($isOwnComment || auth()->user()->isAdmin());
    $isEditingComment = (string) request('edit_comment') === (string) $comment->id;
    $isReplyTarget = (string) request('reply_to') === (string) $comment->id || (string) old('parent_id') === (string) $comment->id;
    $activeThreadTargetId = request('edit_comment') ?? request('reply_to') ?? old('parent_id');
    $containsThreadTarget = function (string $parentId, string $targetId) use (&$containsThreadTarget, $commentsByParentId): bool {
        $children = $commentsByParentId->get((int) $parentId, collect());

        foreach ($children as $child) {
            if ((string) $child->id === $targetId) {
                return true;
            }

            if ($containsThreadTarget((string) $child->id, $targetId)) {
                return true;
            }
        }

        return false;
    };
    $shouldExpandReplies = $childComments->isNotEmpty()
        && $activeThreadTargetId !== null
        && $containsThreadTarget((string) $comment->id, (string) $activeThreadTargetId);
    $currentUserCommentReaction = $currentUserCommentReactions[$comment->id] ?? null;
    $commentReactionSummary = $commentReactionSummaries[$comment->id] ?? [];

    $sortedCommentReactions = collect($commentReactionSummary)
        ->map(fn ($total, $reaction) => [
            'key' => $reaction,
            'total' => (int) $total,
            'meta' => $reactionOptions[$reaction] ?? ['emoji' => '🙂', 'label' => ucfirst((string) $reaction)],
        ])
        ->sortByDesc('total')
        ->values();

    $topCommentReactions = $sortedCommentReactions->take(3);
    if ($topCommentReactions->isEmpty()) {
        $topCommentReactions = collect([
            [
                'key' => 'like',
                'total' => 0,
                'meta' => $reactionOptions['like'] ?? ['emoji' => '👍', 'label' => 'Like'],
            ],
            [
                'key' => 'dislike',
                'total' => 0,
                'meta' => $reactionOptions['dislike'] ?? ['emoji' => '👎', 'label' => 'Dislike'],
            ],
        ]);
    }

    $otherCommentReactionsCount = (int) $sortedCommentReactions->slice(3)->sum('total');
    $commentReactionMenuItems = collect($reactionOptions)
        ->map(function ($meta, $reactionKey) use ($commentReactionSummary) {
            return [
                'key' => $reactionKey,
                'meta' => $meta,
                'total' => (int) ($commentReactionSummary[$reactionKey] ?? 0),
            ];
        })
        ->sortByDesc('total')
        ->values();
    $topCommentReactionKeys = $topCommentReactions->pluck('key')->all();
    $otherReactionMenuItems = $commentReactionMenuItems
        ->filter(fn ($item) => ! in_array($item['key'], $topCommentReactionKeys, true))
        ->values();
@endphp

<div id="comment-{{ $comment->id }}" class="rounded-2xl border p-4 transition {{ $isOwnComment ? 'border-cyan-400/35 bg-cyan-400/10 ring-1 ring-cyan-300/20' : 'border-white/10 bg-slate-950/70' }} {{ $depth > 0 ? 'ml-6 mt-4' : '' }}">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-slate-100">{{ $comment->user?->name ?? 'User' }}</span>
                @if ($depth > 0)
                    <span class="rounded-full border border-cyan-400/30 bg-cyan-400/10 px-2 py-0.5 text-[11px] uppercase tracking-[0.18em] text-cyan-200">Reply</span>
                @endif
            </div>
            <p class="mt-1 text-xs uppercase tracking-[0.22em] text-slate-500">{{ $comment->created_at?->format('Y/m/d H:i') }}</p>
        </div>

        @if (auth()->check())
            <div class="relative shrink-0" data-comment-menu-wrapper>
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-white/5 text-slate-300 transition hover:border-cyan-400/30 hover:bg-cyan-400/10 hover:text-cyan-200"
                    data-comment-menu-button
                    aria-expanded="false"
                    aria-controls="comment-menu-{{ $comment->id }}"
                >
                    <span class="text-xl leading-none">⋮</span>
                </button>

                <div
                    id="comment-menu-{{ $comment->id }}"
                    class="absolute right-0 z-20 mt-2 hidden w-56 overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-2xl shadow-black/40"
                    data-comment-menu
                >
                    @if ($canManageComment)
                        <a
                            href="{{ route('posts.show', $post) }}?comment_sort={{ $commentSort }}&edit_comment={{ $comment->id }}#comment-{{ $comment->id }}"
                            class="flex items-center gap-3 px-4 py-3 text-sm text-slate-200 transition hover:bg-cyan-400/10 hover:text-cyan-200"
                        >
                            <span class="text-cyan-300">✎</span>
                            コメントを編集
                        </a>
                        <form method="POST" action="{{ route('posts.comments.destroy', [$post, $comment]) }}" data-ajax="comment-delete">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm text-rose-200 transition hover:bg-rose-400/10 hover:text-rose-100">
                                <span class="text-rose-300">×</span>
                                コメントを削除
                            </button>
                        </form>
                    @else
                        <button type="button" disabled class="flex w-full cursor-not-allowed items-center gap-3 px-4 py-3 text-left text-sm text-slate-500">
                            <span>⚑</span>
                            ユーザーを報告
                        </button>
                        <button type="button" disabled class="flex w-full cursor-not-allowed items-center gap-3 px-4 py-3 text-left text-sm text-slate-500">
                            <span>⛔</span>
                            ユーザーをブロック
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if ($isEditingComment && $canManageComment)
        <form method="POST" action="{{ route('posts.comments.update', [$post, $comment]) }}" class="mt-3 space-y-3 rounded-2xl border border-cyan-400/30 bg-cyan-400/5 p-4" data-ajax="comment-edit">
            @csrf
            @method('PATCH')
            <label for="edit-comment-content-{{ $comment->id }}" class="block text-xs uppercase tracking-[0.22em] text-cyan-300">コメントを編集</label>
            <textarea
                id="edit-comment-content-{{ $comment->id }}"
                name="content"
                rows="4"
                class="w-full rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none"
            >{{ old('content', $comment->content) }}</textarea>

            @error('content')
                <p class="text-sm text-rose-300">{{ $message }}</p>
            @enderror

            <div class="flex flex-wrap gap-3">
                <button class="rounded-full bg-cyan-400 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-300">更新する</button>
                <a href="{{ route('posts.show', $post) }}?comment_sort={{ $commentSort }}#comment-{{ $comment->id }}" class="rounded-full border border-white/10 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/10">
                    キャンセル
                </a>
            </div>
        </form>
    @else
        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-200">{{ $comment->content }}</p>
    @endif

    <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-slate-300">
        @auth
            @foreach ($topCommentReactions as $item)
                <form method="POST" action="{{ route('posts.comments.reaction.toggle', [$post, $comment]) }}" data-ajax="reaction">
                    @csrf
                    <input type="hidden" name="reaction" value="{{ $item['key'] }}">
                    <input type="hidden" name="comment_sort" value="{{ $commentSort }}">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full border px-3 py-1 transition {{ $currentUserCommentReaction === $item['key'] ? 'border-cyan-400/40 bg-cyan-400/20 text-cyan-100' : 'border-white/10 bg-white/5 text-slate-200 hover:bg-white/10' }}">
                        <span>{{ $item['meta']['emoji'] }}</span>
                        <span class="text-xs text-slate-300">{{ $item['total'] }}</span>
                    </button>
                </form>
            @endforeach

            <div class="relative" data-reaction-menu-wrapper>
                <button
                    type="button"
                    class="rounded-full border border-white/10 bg-white/5 px-3 py-1 transition hover:bg-white/10"
                    data-reaction-menu-button
                    aria-expanded="false"
                    aria-controls="comment-reaction-menu-{{ $comment->id }}"
                >
                    Others{{ $otherCommentReactionsCount > 0 ? ' +' . $otherCommentReactionsCount : '' }} ▾
                </button>

                <div id="comment-reaction-menu-{{ $comment->id }}" class="absolute left-0 z-20 mt-2 hidden w-64 overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-2xl shadow-black/40" data-reaction-menu>
                    <div class="border-b border-white/10 px-4 py-3 text-xs uppercase tracking-[0.22em] text-slate-400">Other Reactions</div>
                    <div class="max-h-80 overflow-y-auto p-2">
                        @forelse ($otherReactionMenuItems as $item)
                            <form method="POST" action="{{ route('posts.comments.reaction.toggle', [$post, $comment]) }}" data-ajax="reaction">
                                @csrf
                                <input type="hidden" name="reaction" value="{{ $item['key'] }}">
                                <input type="hidden" name="comment_sort" value="{{ $commentSort }}">
                                <button type="submit" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm transition {{ $currentUserCommentReaction === $item['key'] ? 'bg-cyan-400/15 text-cyan-100' : 'text-slate-200 hover:bg-white/10' }}">
                                    <span>{{ $item['meta']['emoji'] }} {{ $item['meta']['label'] }}</span>
                                    <span class="text-xs text-slate-400">{{ $item['total'] }}</span>
                                </button>
                            </form>
                        @empty
                            <div class="px-3 py-2 text-xs text-slate-500">No other reactions.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            @foreach ($topCommentReactions as $item)
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-slate-200 transition hover:bg-white/10"
                    data-guest-auth-warning-trigger
                    data-guest-auth-warning-title="リアクションにはログインが必要です"
                    data-guest-auth-warning-message="コメントにリアクションするにはログインまたは会員登録をしてください。"
                >
                    <span>{{ $item['meta']['emoji'] }}</span>
                    <span class="text-xs text-slate-300">{{ $item['total'] }}</span>
                </button>
            @endforeach
            <button
                type="button"
                class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-slate-200 transition hover:bg-white/10"
                data-guest-auth-warning-trigger
                data-guest-auth-warning-title="リアクションにはログインが必要です"
                data-guest-auth-warning-message="コメントにリアクションするにはログインまたは会員登録をしてください。"
            >
                Others ▾
            </button>
        @endauth

        <button type="button" class="rounded-full border border-white/10 bg-white/5 px-3 py-1 transition hover:bg-white/10" data-toggle-replies data-comment-id="{{ $comment->id }}">
            Replies: {{ $comment->replies_count ?? 0 }}
        </button>
    </div>

    <div class="mt-4 border-t border-white/10 pt-4 {{ $isReplyTarget ? '' : 'hidden' }}" data-reply-form-container="{{ $comment->id }}">
        @auth
            <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="space-y-3" data-ajax="reply">
                @csrf
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <input type="hidden" name="comment_sort" value="{{ $commentSort }}">
                <label for="reply-content-{{ $comment->id }}" class="block text-xs uppercase tracking-[0.22em] text-cyan-300">返信する</label>
                <textarea id="reply-content-{{ $comment->id }}" name="content" rows="4" class="w-full rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none" data-reply-textarea="{{ $comment->id }}">{{ $isReplyTarget ? old('content') : '' }}</textarea>
                @error('content')
                    @if ($isReplyTarget)
                        <p class="text-sm text-rose-300">{{ $message }}</p>
                    @endif
                @enderror
                @error('parent_id')
                    @if ($isReplyTarget)
                        <p class="text-sm text-rose-300">{{ $message }}</p>
                    @endif
                @enderror
                <div class="flex flex-wrap gap-3">
                    <button class="rounded-full bg-cyan-400 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-300">返信する</button>
                    <a href="{{ route('posts.show', $post) }}?comment_sort={{ $commentSort }}#comment-{{ $comment->id }}" class="rounded-full border border-white/10 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/10">
                        キャンセル
                    </a>
                </div>
            </form>
        @else
            <div class="space-y-3">
                <p class="text-sm text-slate-300">コメントを投稿するにはログインが必要です。</p>
                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="rounded-full bg-cyan-400 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-300"
                        data-guest-auth-warning-trigger
                        data-guest-auth-warning-title="コメントにはログインが必要です"
                        data-guest-auth-warning-message="コメントを投稿するにはログインまたは会員登録をしてください。"
                    >
                        ログイン / 会員登録
                    </button>
                </div>
            </div>
        @endauth
    </div>

    @if ($childComments->isNotEmpty())
        <div class="mt-4 space-y-4 border-l border-white/10 pl-4 {{ $shouldExpandReplies ? '' : 'hidden' }}" data-replies-container="{{ $comment->id }}">
            @foreach ($childComments as $childComment)
                @include('posts.partials.comment', [
                    'comment' => $childComment,
                    'post' => $post,
                    'commentsByParentId' => $commentsByParentId,
                    'commentReactionSummaries' => $commentReactionSummaries,
                    'currentUserCommentReactions' => $currentUserCommentReactions,
                    'reactionOptions' => $reactionOptions,
                    'commentSort' => $commentSort,
                    'depth' => $depth + 1,
                ])
            @endforeach
        </div>
    @endif
</div>

@extends('layouts.app')

@section('content')
    @php
        $sortedPostReactions = collect($postReactionsSummary)
            ->map(fn ($total, $reaction) => [
                'key' => $reaction,
                'total' => (int) $total,
                'meta' => $reactionOptions[$reaction] ?? ['emoji' => '🙂', 'label' => ucfirst((string) $reaction)],
            ])
            ->sortByDesc('total')
            ->values();

        $topPostReactions = $sortedPostReactions->take(3);
        if ($topPostReactions->isEmpty()) {
            $topPostReactions = collect([
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

        $postReactionMenuItems = collect($reactionOptions)
            ->map(function ($meta, $reactionKey) use ($postReactionsSummary) {
                return [
                    'key' => $reactionKey,
                    'meta' => $meta,
                    'total' => (int) ($postReactionsSummary[$reactionKey] ?? 0),
                ];
            })
            ->sortByDesc('total')
            ->values();
        $topPostReactionKeys = $topPostReactions->pluck('key')->all();
        $otherPostReactionMenuItems = $postReactionMenuItems
            ->filter(fn ($item) => ! in_array($item['key'], $topPostReactionKeys, true))
            ->values();
        $otherPostReactionsCount = (int) $otherPostReactionMenuItems->sum('total');
    @endphp

    <article class="mx-auto w-full max-w-4xl space-y-6">
        <section class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-8 shadow-2xl shadow-cyan-950/20">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.28em] text-cyan-300">Post {{ $post->id }}</p>
                    <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white">{{ $post->title }}</h1>
                </div>
                <div class="rounded-full border border-white/10 px-3 py-1 text-xs text-slate-300">{{ $post->views_count }} views</div>
            </div>

            <p class="mt-4 text-sm text-slate-500">{{ $post->created_at?->format('Y/m/d H:i') }}</p>

            <div class="prose prose-invert mt-6 max-w-none prose-p:text-slate-300 prose-headings:text-white">
                <p>{{ $post->content }}</p>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-white/10 pt-4 text-sm text-slate-300" data-post-reactions>
                @auth
                    @foreach ($topPostReactions as $item)
                        <form method="POST" action="{{ route('posts.reaction.toggle', $post) }}" data-ajax="reaction">
                            @csrf
                            <input type="hidden" name="reaction" value="{{ $item['key'] }}">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-full border px-3 py-1 transition {{ $currentUserPostReaction === $item['key'] ? 'border-cyan-400/40 bg-cyan-400/20 text-cyan-100' : 'border-white/10 bg-white/5 text-slate-200 hover:bg-white/10' }}">
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
                            aria-controls="post-reaction-menu"
                        >
                            Others{{ $otherPostReactionsCount > 0 ? ' +' . $otherPostReactionsCount : '' }} ▾
                        </button>

                        <div id="post-reaction-menu" class="absolute right-0 z-30 mt-2 hidden w-72 overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-2xl shadow-black/40" data-reaction-menu>
                            <div class="border-b border-white/10 px-4 py-3 text-xs uppercase tracking-[0.22em] text-slate-400">Other Reactions</div>
                            <div class="max-h-80 overflow-y-auto p-2">
                                @forelse ($otherPostReactionMenuItems as $item)
                                    <form method="POST" action="{{ route('posts.reaction.toggle', $post) }}" data-ajax="reaction">
                                        @csrf
                                        <input type="hidden" name="reaction" value="{{ $item['key'] }}">
                                        <button type="submit" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm transition {{ $currentUserPostReaction === $item['key'] ? 'bg-cyan-400/15 text-cyan-100' : 'text-slate-200 hover:bg-white/10' }}">
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
                    @foreach ($topPostReactions as $item)
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-slate-200 transition hover:bg-white/10"
                            data-guest-auth-warning-trigger
                            data-guest-auth-warning-title="リアクションにはログインが必要です"
                            data-guest-auth-warning-message="投稿にリアクションするにはログインまたは会員登録をしてください。"
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
                        data-guest-auth-warning-message="投稿にリアクションするにはログインまたは会員登録をしてください。"
                    >
                        Others ▾
                    </button>
                @endauth

                @auth
                    <a href="#comment-form" class="rounded-full border border-white/10 bg-white/5 px-3 py-1 transition hover:bg-white/10">{{ $post->comments_count }} comments</a>
                @else
                    <a href="#comments" class="rounded-full border border-white/10 bg-white/5 px-3 py-1 transition hover:bg-white/10">{{ $post->comments_count }} comments</a>
                @endauth
            </div>
        </section>

        <section id="comment-form" class="scroll-mt-32 rounded-[1.75rem] border border-white/10 bg-white/5 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-white">コメント投稿</h2>
                    <p class="mt-1 text-sm text-slate-400">新しいコメントは上部、返信は各コメントの Reply から送れます。</p>
                </div>
                <div class="flex flex-wrap gap-2 text-sm">
                    <a href="{{ route('posts.show', ['post' => $post->id, 'comment_sort' => 'new']) }}#comments" class="rounded-full border px-3 py-1.5 transition {{ $commentSort === 'new' ? 'border-cyan-400/40 bg-cyan-400/20 text-cyan-100' : 'border-white/10 text-slate-200 hover:bg-white/10' }}">
                        New
                    </a>
                    <a href="{{ route('posts.show', ['post' => $post->id, 'comment_sort' => 'popular']) }}#comments" class="rounded-full border px-3 py-1.5 transition {{ $commentSort === 'popular' ? 'border-cyan-400/40 bg-cyan-400/20 text-cyan-100' : 'border-white/10 text-slate-200 hover:bg-white/10' }}">
                        Popular
                    </a>
                </div>
            </div>

            @auth
                <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mt-4 space-y-4" data-ajax="comment">
                    @csrf
                    <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                    <input type="hidden" name="comment_sort" value="{{ $commentSort }}">
                    <div>
                        <label for="content" class="mb-2 block text-sm text-slate-300">コメント内容</label>
                        <textarea id="content" name="content" rows="5" class="w-full rounded-2xl border border-white/10 bg-slate-950/80 px-4 py-3 text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none" placeholder="10文字以上100文字以内で入力してください">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <input type="hidden" name="parent_id" value="">
                    <button class="inline-flex items-center rounded-full bg-cyan-400 px-5 py-3 font-medium text-slate-950 transition hover:bg-cyan-300">投稿する</button>
                </form>
            @else
                <div class="mt-4 rounded-2xl border border-white/10 bg-slate-950/60 p-4 text-sm leading-6 text-slate-300">
                    <p>コメント投稿にはログインが必要です。</p>
                    <button
                        type="button"
                        class="mt-3 inline-flex items-center rounded-full bg-cyan-400 px-4 py-2 font-medium text-slate-950 transition hover:bg-cyan-300"
                        data-guest-auth-warning-trigger
                        data-guest-auth-warning-title="コメントにはログインが必要です"
                        data-guest-auth-warning-message="コメントを投稿するにはログインまたは会員登録をしてください。"
                    >
                        ログイン / 会員登録
                    </button>
                </div>
            @endauth
        </section>

        <section id="comments" class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6">
            <h2 class="text-xl font-semibold text-white">コメント</h2>
            <div class="mt-4 space-y-4">
                @forelse ($commentsByParentId->get(0, collect()) as $comment)
                    @include('posts.partials.comment', [
                        'comment' => $comment,
                        'post' => $post,
                        'commentsByParentId' => $commentsByParentId,
                        'commentReactionSummaries' => $commentReactionSummaries,
                        'currentUserCommentReactions' => $currentUserCommentReactions,
                        'reactionOptions' => $reactionOptions,
                        'commentSort' => $commentSort,
                        'depth' => 0,
                    ])
                @empty
                    <div class="rounded-2xl border border-dashed border-white/15 bg-slate-950/50 p-4 text-sm text-slate-400">
                        まだコメントがありません。
                    </div>
                @endforelse
            </div>
        </section>
    </article>

@endsection

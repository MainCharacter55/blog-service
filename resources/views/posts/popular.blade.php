@extends('layouts.app')

@section('content')
    <section class="mx-auto w-full max-w-4xl space-y-6">
        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 backdrop-blur">
            <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">Feed</p>
            <h1 class="mt-2 text-3xl font-semibold text-white">Popular Posts</h1>
            <p class="mt-3 text-sm text-slate-300">人気スコア (reactions + comments + views) で降順表示しています。</p>
        </div>

        <div class="grid gap-4">
            @forelse ($posts as $post)
                @php
                    $postReactionSummary = $postReactionSummaries[$post->id] ?? [];
                    $currentUserPostReaction = $currentUserPostReactions[$post->id] ?? null;

                    $sortedPostReactions = collect($postReactionSummary)
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
                        ->map(function ($meta, $reactionKey) use ($postReactionSummary) {
                            return [
                                'key' => $reactionKey,
                                'meta' => $meta,
                                'total' => (int) ($postReactionSummary[$reactionKey] ?? 0),
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

                <div class="rounded-2xl border border-white/10 bg-slate-950/60 p-5 transition hover:-translate-y-0.5 hover:border-cyan-400/30 hover:bg-slate-950" data-post-card data-post-url="{{ route('posts.show', $post) }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Post {{ $post->id }}</p>
                            <h3 class="mt-2 text-xl font-semibold text-white">{{ $post->title }}</h3>
                        </div>
                        <div class="rounded-full border border-white/10 px-3 py-1 text-xs text-slate-300">{{ $post->views_count }} views</div>
                    </div>

                    <p class="mt-3 text-sm leading-7 text-slate-300">{{ \Illuminate\Support\Str::limit($post->content, 140) }}</p>

                    <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-slate-300">
                        @auth
                            @foreach ($topPostReactions as $item)
                                <form method="POST" action="{{ route('posts.reaction.toggle', $post) }}">
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
                                    aria-controls="popular-post-reaction-menu-{{ $post->id }}"
                                >
                                    Others{{ $otherPostReactionsCount > 0 ? ' +' . $otherPostReactionsCount : '' }} ▾
                                </button>

                                <div id="popular-post-reaction-menu-{{ $post->id }}" class="absolute left-0 z-20 mt-2 hidden w-64 overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-2xl shadow-black/40" data-reaction-menu>
                                    <div class="border-b border-white/10 px-4 py-3 text-xs uppercase tracking-[0.22em] text-slate-400">Other Reactions</div>
                                    <div class="max-h-80 overflow-y-auto p-2">
                                        @forelse ($otherPostReactionMenuItems as $item)
                                            <form method="POST" action="{{ route('posts.reaction.toggle', $post) }}">
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
                            <a href="{{ route('posts.show', $post) }}#comment-form" class="rounded-full border border-white/10 bg-white/5 px-3 py-1 transition hover:bg-white/10">{{ $post->comments_count }} comments</a>
                        @else
                            <a href="{{ route('posts.show', $post) }}#comments" class="rounded-full border border-white/10 bg-white/5 px-3 py-1 transition hover:bg-white/10">{{ $post->comments_count }} comments</a>
                        @endauth
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-white/15 bg-white/5 p-8 text-sm text-slate-400">
                    まだ投稿がありません。Seeder でサンプル投稿を追加してください。
                </div>
            @endforelse
        </div>

        @if ($posts->hasPages())
            <div class="rounded-2xl border border-white/10 bg-slate-900/70 px-4 py-3">
                <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="Popular posts pagination">
                    @if ($posts->onFirstPage())
                        <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-white/10 bg-slate-800/60 px-3 text-sm text-slate-500">&lsaquo;</span>
                    @else
                        <a href="{{ $posts->previousPageUrl() }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-white/10 bg-slate-800/60 px-3 text-sm text-slate-200 transition hover:border-cyan-400/40 hover:bg-cyan-400/10 hover:text-cyan-200">&lsaquo;</a>
                    @endif

                    @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                        @if ($page === $posts->currentPage())
                            <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-cyan-400/40 bg-cyan-400/20 px-3 text-sm font-semibold text-cyan-100">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-white/10 bg-slate-800/60 px-3 text-sm text-slate-200 transition hover:border-cyan-400/40 hover:bg-cyan-400/10 hover:text-cyan-200">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($posts->hasMorePages())
                        <a href="{{ $posts->nextPageUrl() }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-white/10 bg-slate-800/60 px-3 text-sm text-slate-200 transition hover:border-cyan-400/40 hover:bg-cyan-400/10 hover:text-cyan-200">&rsaquo;</a>
                    @else
                        <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-white/10 bg-slate-800/60 px-3 text-sm text-slate-500">&rsaquo;</span>
                    @endif
                </nav>
            </div>
        @endif
    </section>

@endsection

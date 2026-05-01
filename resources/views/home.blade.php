@extends('layouts.app')

@section('content')
    <section class="mx-auto w-full max-w-4xl space-y-6">
        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-2xl shadow-cyan-950/20 backdrop-blur">
            <p class="text-sm uppercase tracking-[0.28em] text-cyan-300">blog-service</p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white sm:text-5xl">
                気軽に読めて、すぐ投稿できるシンプルなブログ。
            </h1>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-300">
                投稿の一覧、コメント閲覧、ログイン後の投稿までをひとつにまとめた軽量なブログ画面です。
                API と Blade の両方を使いながら、学習しやすい構成にしています。
            </p>

            <div class="mt-6 flex flex-wrap gap-3 text-sm">
                <a href="{{ route('posts.recent') }}" class="rounded-full bg-white/10 px-4 py-2 text-slate-200 transition hover:bg-cyan-400/20 hover:text-cyan-100">Recent</a>
                <a href="{{ route('posts.popular') }}" class="rounded-full bg-white/10 px-4 py-2 text-slate-200 transition hover:bg-cyan-400/20 hover:text-cyan-100">Popular</a>
                <span class="rounded-full bg-white/10 px-4 py-2 text-slate-200">Comment posting</span>
                <span class="rounded-full bg-white/10 px-4 py-2 text-slate-200">Sanctum auth</span>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">Getting started</p>
                <h2 class="mt-2 text-lg font-semibold text-white">はじめ方</h2>
                <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-300">
                    <li>1. Register で会員登録用メールを送信</li>
                    <li>2. Mailpit でトークンを確認</li>
                    <li>3. ログインしてコメントを投稿</li>
                </ul>
            </div>

            <div class="rounded-[1.5rem] border border-white/10 bg-gradient-to-br from-cyan-400/15 to-emerald-400/10 p-5">
                <p class="text-xs uppercase tracking-[0.24em] text-cyan-200">Brand</p>
                <h2 class="mt-2 text-lg font-semibold text-white">blog-service</h2>
                <p class="mt-3 text-sm leading-6 text-slate-200/90">
                    backend tutorial という仮名から、ブログ用途にそのまま使える名前へ整理しました。
                </p>
            </div>
        </div>

        <div class="rounded-[1.75rem] border border-white/10 bg-slate-900/70 p-6">
            <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">Explore</p>
            <h2 class="mt-2 text-2xl font-semibold text-white">Browse Posts</h2>
            <p class="mt-3 text-sm leading-7 text-slate-300">
                ホームフィードは非表示にしました。Recent または Popular から投稿一覧を確認できます。
            </p>

            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('posts.recent') }}" class="rounded-full bg-cyan-400 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-300">Go to Recent</a>
                <a href="{{ route('posts.popular') }}" class="rounded-full border border-white/15 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/10">Go to Popular</a>
            </div>
        </div>
    </section>
@endsection

English text at bottom.
---------------------------------------------------

日本語
====

1. 提出課題の概要 / Project Overview
本プロジェクトは、API提供だけでなく、ユーザーが直接利用できるGUI（Blade）を備えたフルスタックのブログアプリケーション 「blog-service」 として構築しました。

取り組んだ課題
基本課題 (Basic Tasks): [完了]
コメント機能の完全なCRUD実装（API）。
MySQLを使用したリレーショナルなDB設計（Users, Posts, Comments）。
Swagger UI / ReDoc によるAPIドキュメントの自動生成。

応用課題 (Applied Tasks): [完了]
ユーザー認証: Laravel Sanctum を使用したセキュアな認証。
二段階登録: メールトークンを使用した会員登録フローの実装。
GUI実装: Laravel Blade を使用し、直感的に操作できるフロントエンドを構築。
追加機能: 投稿やコメントに対するリアクション（いいねなど）機能、およびコメントへの返信（ネスト）機能。

2. 工夫点・アピールポイント (Highlights)
プロダクションを意識したセキュリティ:
WebとAPIの両方でレートリミット（Throttle）を設定。
会員登録時のパスワードポリシーを共通化し、安全性を向上させました。
BOLA（認可）対策として、自分以外のコメントの編集・削除をPolicyで厳密に禁止。

開発環境のこだわり:
Docker Sailを拡張し、ドキュメント閲覧用に ReDoc コンテナを追加。
WSL環境でのファイルパーミッション問題やDocker認証エラーを自力で解決し、安定した開発基盤を構築しました。

将来的な拡張性:
単なる課題に留めず、将来的に自身のPython製ポートフォリオサイトと連携させる「個人ブログのバックエンド」として再定義し、独自リポジトリ blog-service として管理しています。

3. 解決した問題・得た学び (Problem Solving & Learning)
Gitのトラブルシューティング:
git reset --hard による予期せぬコード紛失を経験しましたが、VS Codeの「Local History」とGitHub Copilotの履歴を駆使して復旧させました。この経験から、コミットの重要性とIDEのバックエンド機能への理解が深まりました。

マルチ言語・マルチスタックの理解:
PHP/Laravelでのバックエンド構築を通じ、Python(Django)やJava(JakartaEE)と比較した際のLaravelの規約の利便性や開発スピードの速さを実感しました。

RESTful API設計:
リソースクラス（UserResource等）を介することで、DBの構造を隠蔽しながらクライアントに必要なデータだけを返す設計手法を学びました。

4. 注意して見てほしい点 (Review Focus)
コードの共通化: AppServiceProvider でのレートリミッター定義や、バリデーションロジックの共通化に配慮しました。

ネスト構造: コメントに対する返信機能（親子関係）が正しくDBおよびAPIで処理されているかをご確認いただけますと幸いです。
---------------------------------------------------

起動・利用方法 (Usage)
起動
Bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed

アクセス
Web UI (Blade): http://localhost/

API Documentation:
Swagger: http://localhost:8002/
ReDoc: http://localhost:8003/
Mail Confirmation (Mailpit): http://localhost:8025/
---------------------------------------------------

Review1 後の改善内容 (Improvement After Review1)
- Swagger / ReDoc: 実装と仕様の差分を見直し、`routes/api.php` のエンドポイントと `docs/api/comment-api.yml` のパス・操作定義が一致することを確認しました。さらに、仕様を修正し、パスワード要件（最小12文字、大小英字・数字・記号を含む）、レスポンス構造（message/data フィールド、token_type）、ネストコメント対応（parent_id）、ユーザースキーマを追加しました。
- PHPDoc: API Controller および FormRequest にクラス単位の PHPDoc を追加し、責務と意図が分かるように改善しました。
- 設計（SRP / Fat Controller 対策）: API のバリデーション責務を FormRequest に集約し、Controller 側の重複チェックを削除しました。
- Route Model Binding: API コメント更新・削除での冗長な関連チェックを削除し、`scopeBindings` と Policy を前提にシンプル化しました。
- Request 命名規約: `StoreApi* / UpdateApi* / StoreWeb* / UpdateWeb*` 形式へ統一しました。
- ReDoc: `docker-compose.yml` の設定を修正し、`http://localhost:8003` で表示できるようにしました。
---------------------------------------------------

English
====

1. Project Overview
This project implements a full-stack blog application, `blog-service`, which provides both an API and a Blade-based GUI for users.

Tasks completed
- Basic Tasks: Comment CRUD via API, relational DB design (Users, Posts, Comments), and automatic API docs generation (Swagger / ReDoc).
- Applied Tasks: Authentication using Laravel Sanctum, two-step registration with email tokens, Blade-based GUI, and reaction/reply features for posts and comments.

2. Highlights
- Security-minded configuration: rate limiting on Web and API, centralized password policy, and strict authorization via Policies (BOLA protection).
- Development environment: Docker Sail plus a ReDoc container for API browsing.
- Extensibility: Designed as a backend suitable for integration with other frontends or personal portfolio projects.

3. Problem Solving & Learning
- Recovering from Git issues reinforced commit discipline and use of IDE history tools.
- Built fluency with RESTful API design and `JsonResource` usage to present controlled API responses.

4. Review Focus
- Please verify validation and common logic centralization (e.g., rate limiter, FormRequest usage).
- Please check nested comment reply handling at DB and API levels.
---------------------------------------------------

Usage / Run
- Start the app: `./vendor/bin/sail up -d`
- Run migrations and seeders: `./vendor/bin/sail artisan migrate --seed`
- Web UI: http://localhost/
- Swagger UI: http://localhost:8002/
- ReDoc: http://localhost:8003/
- Mailpit: http://localhost:8025/
---------------------------------------------------

Review1 後の改善内容 (Improvement After Review1)
- Swagger / ReDoc: Rechecked implementation-vs-spec alignment and verified API paths/operations in `docs/api/comment-api.yml` match `routes/api.php`. Updated spec with correct password requirements (min 12 chars, mixed case, numbers, symbols), response structures (message/data fields, token_type), nested comment support (parent_id), and User schema.
- PHPDoc: Added class-level PHPDoc to API controllers and FormRequest classes to improve readability and tooling support.
- Design (SRP / avoiding Fat Controller): Centralized API validation in FormRequest classes and removed duplicated validation logic from controllers.
- Route Model Binding: Removed redundant parent-child checks in API comment update/delete and relied on `scopeBindings` + policies.
- Request naming convention: Standardized request class names to `StoreApi* / UpdateApi* / StoreWeb* / UpdateWeb*`.
- ReDoc: Updated `docker-compose.yml` so ReDoc is available at `http://localhost:8003`.
---------------------------------------------------

<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebCommentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_detail_popular_comment_sort_loads_successfully(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $parent = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $parent->id,
        ]);

        $response = $this->get(route('posts.show', [
            'post' => $post,
            'comment_sort' => 'popular',
        ]));

        $response->assertOk();
    }

    public function test_comment_owner_can_open_inline_edit_mode_and_update_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => '編集前のコメント本文です。',
        ]);

        $this->actingAs($user);

        $showResponse = $this->get(route('posts.show', [
            'post' => $post,
            'comment_sort' => 'new',
            'edit_comment' => $comment->id,
        ]));

        $showResponse->assertOk();
        $showResponse->assertSee('コメントを編集');
        $showResponse->assertSee('更新する');

        $updateResponse = $this->patch(route('posts.comments.update', [$post, $comment]), [
            'content' => 'これは更新後のコメント本文です。',
        ]);

        $updateResponse->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'これは更新後のコメント本文です。',
        ]);
    }
}

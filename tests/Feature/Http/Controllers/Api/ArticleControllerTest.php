<?php

namespace Http\Controllers\Api;

use App\Models\Article;
use App\Models\Provider;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{

    public function test_index_returns_200_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Article::all()->each->delete();

        Article::factory()->count(5)->create();

        $response = $this->get('/api/articles');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    public function test_index_returns_404_status_code_when_no_articles_found(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Article::all()->each->delete();

        $response = $this->get('/api/articles');

        $response->assertStatus(404);
    }

    public function test_index_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        Article::all()->each->delete();

        Article::factory()->count(5)->create();

        $response = $this->get('/api/articles');

        $response->assertStatus(403);
    }

    public function test_show_returns_200_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['read']
        );

        $article = Article::factory()->create();

        $response = $this->get('/api/articles/' . $article->id);

        $response->assertStatus(200);
    }

    public function test_show_returns_an_article(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['read']
        );

        $article = Article::factory()->create();

        $response = $this->get('/api/articles/' . $article->id);

        $response->assertJsonFragment([
            'id' => $article->id,
            'title' => $article->title,
        ]);
    }

    public function test_show_returns_404_status_code_when_article_not_found(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['read']
        );

        $response = $this->get('/api/articles/1');

        $response->assertStatus(404);
    }

    public function test_show_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $article = Article::factory()->create();

        $response = $this->get('/api/articles/' . $article->id);

        $response->assertStatus(403);
    }

    public function test_store_returns_201_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['create']
        );

        $article = [
            'title' => fake()->words(3, true),
            'provider' => Provider::factory()->create()->id,
        ];

        $response = $this->post('/api/articles', $article);

        $response->assertStatus(201);
    }

    public function test_store_returns_422_status_code_when_validation_fails(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['create']
        );

        $response = $this->post('/api/articles', []);

        $response->assertStatus(422);
    }

    public function test_store_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $response = $this->post('/api/articles', [
            'title' => fake()->words(3, true),
            'provider' => Provider::factory()->create()->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_update_returns_202_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['update']
        );

        $article = Article::factory()->create();

        $response = $this->put('/api/articles/' . $article->id, [
            'title' => fake()->words(3, true),
            'provider' => Provider::factory()->create()->id,
        ]);

        $response->assertStatus(202);
    }

    public function test_update_returns_an_article(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['update']
        );

        $article = Article::factory()->create();

        $newTitle = fake()->words(3, true);

        $response = $this->put('/api/articles/' . $article->id, [
            'title' => $newTitle,
            'provider' => Provider::factory()->create()->id,
        ]);

        $response->assertJsonFragment([
            'id' => $article->id,
            'title' => $newTitle,
        ]);
    }

    public function test_update_returns_422_status_code_when_validation_fails(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['update']
        );

        $article = Article::factory()->create();

        $response = $this->put('/api/articles/' . $article->id, ['provider' => true]);

        $response->assertStatus(422);
    }

    public function test_update_returns_400_status_code_when_no_data_sent(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['update']
        );

        $article = Article::factory()->create();

        $response = $this->put('/api/articles/' . $article->id, []);

        $response->assertStatus(400);
    }

    public function test_update_returns_404_status_code_when_article_not_found(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['update']
        );

        $response = $this->put('/api/articles/1', [
            'title' => fake()->words(3, true),
            'provider' => Provider::factory()->create()->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_update_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $article = Article::factory()->create();

        $response = $this->put('/api/articles/' . $article->id, [
            'title' => fake()->words(3, true),
            'provider' => Provider::factory()->create()->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_destroy_returns_202_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['delete']
        );

        $article = Article::factory()->create();

        $response = $this->delete('/api/articles/' . $article->id);

        $response->assertStatus(202);
    }

    public function test_destroy_returns_404_status_code_when_article_not_found(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['delete']
        );

        $response = $this->delete('/api/articles/1');

        $response->assertStatus(404);
    }

    public function test_destroy_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $article = Article::factory()->create();

        $response = $this->delete('/api/articles/' . $article->id);

        $response->assertStatus(403);
    }

    public function test_restore_returns_202_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['restore']
        );

        $article = Article::factory()->create();
        $article->delete();

        $response = $this->post('/api/articles/' . $article->id . '/restore');

        $response->assertStatus(202);
    }

    public function test_restore_returns_404_status_code_when_article_not_found(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['restore']
        );

        $response = $this->post('/api/articles/1/restore');

        $response->assertStatus(404);
    }

    public function test_restore_returns_404_status_code_when_article_not_deleted(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['restore']
        );

        $article = Article::factory()->create();

        $response = $this->post('/api/articles/' . $article->id . '/restore');

        $response->assertStatus(404);
    }

    public function test_restore_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $article = Article::factory()->create();
        $article->delete();

        $response = $this->post('/api/articles/' . $article->id . '/restore');

        $response->assertStatus(403);
    }

    public function test_force_delete_returns_202_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['force-delete']
        );

        $article = Article::factory()->create();
        $article->delete();

        $response = $this->delete('/api/articles/' . $article->id . '/force-delete');

        $response->assertStatus(202);
    }

    public function test_force_delete_returns_404_status_code_when_article_not_found(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['force-delete']
        );

        $response = $this->delete('/api/articles/1/force-delete');

        $response->assertStatus(404);
    }

    public function test_force_delete_returns_404_status_code_when_article_not_deleted(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['force-delete']
        );

        $article = Article::factory()->create();

        $response = $this->delete('/api/articles/' . $article->id . '/force-delete');

        $response->assertStatus(404);
    }

    public function test_force_delete_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $article = Article::factory()->create();
        $article->delete();

        $response = $this->delete('/api/articles/' . $article->id . '/force-delete');

        $response->assertStatus(403);
    }
}

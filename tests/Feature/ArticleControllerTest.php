<?php

namespace Tests\Feature;

use App\Article;
use Tests\TestCase;
use Tests\LaravelTestingTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleControllerTest extends LaravelTestingTestCase
{
    /**
     * Guest could see list of articles.
     *
     * @test
     */
    public function it_allows_anyone_to_see_list_all_articles()
    {
        $response = $this->get(route('get_all_articles'));

        $response->assertSuccessful();
        $response->assertViewIs('articles.index');
        $response->assertViewHas('articles');
    }
    /**
     * A Guest could see a single article.
     *
     * @test
     */
    public function it_allows_anyone_to_see_individual_articles()
    {
        // 1. Get an article to view (randomly)
        $article = Article::get()->random();
        // 2. Generate the route to this article and send a GET request to it
        $response = $this->get(route('view_article', ['id' => $article->id]));
        // 3. Make sure that we are getting the right view (in this case articles.view)
        $response->assertViewIs('articles.view');
        // 4. Make sure that the view returned contains a variable named $article
        $response->assertViewHas('article');
        //5. Make sure that we are getting the article we wanted to access and not another one.
        $returnedArticle = $response->original->article;
        $this->assertEquals($article->id, $returnedArticle->id, 'The returned article is different from one we requested.');
    }

}

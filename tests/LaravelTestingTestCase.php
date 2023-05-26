<?php

namespace Tests;

use App\User;
use App\Article;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class LaravelTestingTestCase extends TestCase
{
    use WithFaker;
    use DatabaseMigrations;
    //use DatabaseTransactions; // return the database to its prior state after each test.

    protected $user;

    public function setUp(){
        parent::setUp();
        $this->artisan('db:seed');
    }
 
    public function getRandomUser()
    {
        $this->user = User::get()->random();
        
        return $this->user;
    }
    
    public function getUserRandomArticle()
    {
        $article = factory(Article::class)->create(['author_id' => $this->user->id]);
        return $article;
    }
    
    public function getAnotherRandomUser()
    {
        return User::where('id', '<>', $this->user->id)->get()->random();
    }
    
    public function getRandomArticleData()
    {
        return [
            "title" => $this->faker->sentence,
            "body" => $this->faker->paragraph
        ];
    }
}

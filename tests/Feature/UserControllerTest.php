<?php

namespace Tests\Feature;

use App\User;
use App\Article;

use Tests\LaravelTestingTestCase;

class UserControllerTest extends LaravelTestingTestCase
{
    //  private $faker ;
    //  public function setUp(): void{
    //     parent::setUp();
    //     $this->faker = Factory::create();
    //  }

    /**
     * A Guest could see a user profile
     * 
     * @test
     */

     public function it_allows_anyone_to_see_user_profiles()
     {
         // 1. Get user  view (randomly)
         $user = User::get()->random();
         // 2. Generate the route to this user and send a GET request to it
         $response = $this->get(route('show_user_profile', ['id' => $user->id]));
         // 3. Make sure that we are getting the right view (in this case users.show)
         $response->assertViewIs('users.show');
         // 4. Make sure that the view returned contains a variable named $user
         $response->assertViewHas('user');
         //5. Make sure that we are getting the article we wanted to access and not another one.
         $returnedUser = $response->original->user;
         $this->assertEquals($user->id, $returnedUser->id, 'The returned user is different from one we requested.');
     }

    /**
     * A Guest could not write a new article and gets 
     * redirected to the signup page instead
     * 
     * @test
     */

     public function it_prevent_non_logged_in_users_from_creating_new_article()
     {
        //1. attempt to access the create_new_article
        $response = $this->get(route('create_new_article'));
        // 2. test whether we got redirected to the login page
        $response->assertRedirect('login');
     }
     
    /**
     * The user visit and get the new_article form
     * 
     * @test
     */

     public function it_allows_logged_in_users_to_create_new_articles()
     {
        //1. attempt to access the create_new_article
        // $response = $this->get(route('create_new_article'));
        // 2. test whether we got redirected to the create article page
        // $response->assertViewIs('articles.create');
        //Get a random user
        $user = User::get()->random();

        //Act as the user we got and request the create_new_article route
        $response = $this->actingAs($user)->get(route('create_new_article'));
         $response->assertViewIs('articles.create');
     }

     /**
      * The use should be able to save the newly cre    ated article.
      * @test
      */
      public function it_allows_logged_in_users_to_save_new_articles(){
       
        $user = User::get()->random();
        $totalNumberOfArticlesBefore = Article::count();

        $data = $this->getRandomArticleData();

        $response = $this->actingAs($user)->post(route('save_new_article'), $data);

        //Get the last article in the DB
        $lastArticleInTheDB = Article::orderBy('id', 'desc')->first();

        // Check if it matches with the one we inserting 

        // One way to check this is by gettiing the last article that was inserted in the DB and compare its data to the data we used to create it. If we get the exact same data, this would mean that our code is working as expected.
        $this->assertEquals($lastArticleInTheDB->title, $data['title'], "the title of the saved article is different from the title we used");

        $this->assertEquals($lastArticleInTheDB->body, $data['body'], "the body of the saved article is different from the title we used");

        /**
         * The artcle is assigned to the right user
         * 
         * @test
         */

         //all we need to do is to test tat the owner of thr newly created article is te one we are acting on behalf

         $this->assertEquals($lastArticleInTheDB->author_id, $user->id, "the owner of the saved article is different from the title we used");

         $totalNumberOfArticlesAfter = Article::count();

         $this->assertEquals($totalNumberOfArticlesAfter, $totalNumberOfArticlesBefore + 1, "the number of total article is supposed to be incremented by 1");

         /**
          * The user is redirected to the article after creating it
          */

          // We just need to assert that we are redirected to the right article

          $response->assertRedirect(route('view_article', ['id' => $lastArticleInTheDB->id]));
      }

    /**
     *  A user could edit her own articles 
     * @test
     * */  

     //The user could visit and get the edit_article form

     public function it_allows_owner_of_an_article_to_edit_it(){
        $user = user::get()->random();
        //1. Get a random user and get one of her articles randomly
        $article = factory(Article::class)->create(['author_id' => $user->id]);
        //2.Request the edit page of this article and ensure that we are getting the right
        // view (i.e the article edit form)

        $response = $this->actingAs($user)->get(route('edit_article', ['id' => $article->id]));

        // Ensure that we are getting the rigt view 
        $response->assertViewIs('articles.edit');

        //With the right data
        $returnedArticle = $response->original->article;
        $this->assertEquals($article->id, $returnedArticle->id, 'The returned article is different from the one we want to edit');
     }

    /**
     * The user should be able to save the updated article
     * 
     * @test
     */
    public function it_allows_owner_of_an_article_to_save_edits()
    {
        $user = $this->getRandomUser();
        $article = $this->getUserRandomArticle();
        $totalNumberOfArticlesBefore = Article::count();
        
        $articleNewData = $this->getRandomArticleData();
       $response = $this->actingAs($user)->json('POST', route('update_article', ['id' => $article->id]), $articleNewData); 
       // get a fresh copy of the article
       $article->refresh();
       $this->assertEquals($article->title, $articleNewData['title'], "the title of the article wasn't updated");
       $this->assertEquals($article->body, $articleNewData['body'], "the title of the article wasn't updated");
       $this->assertEquals($article->author_id, $user->id, "the article was assigned to an other user");
       $totalNumberOfArticlesAfter = Article::count();
       $this->assertEquals($totalNumberOfArticlesAfter, $totalNumberOfArticlesBefore, "the number of total article is supposed to stay the same");
       // ensure that we are redirected to the same article after updating it
       $response->assertRedirect(route('view_article', ['id' => $article->id]));
       
    }

    /**
     * A user could delete her own articles and get redirected to the all articles page with a success message
     * 
     * @test
     */

     public function it_allows_owner_of_an_article_to_delete_it()
     {
        $user = factory(User::class)->create();
        
        // Create at least one article associated with the user
        $article = factory(Article::class)->create([
            'author_id' => $user->id,
        ]);
     
        $totalNumberOfArticlesBefore = Article::count();
        
        $response = $this->actingAs($user)->get(route('delete_article', ['id' => $article->id]));
        
        // Assert that the article is no longer present in the database
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        
        $totalNumberOfArticlesAfter = Article::count();
        
        $this->assertEquals($totalNumberOfArticlesAfter, $totalNumberOfArticlesBefore - 1, "the number of article should decrement by 1 ");
        
        // ensure that we are getting the right view
        $response->assertRedirect(route('get_all_articles'));
        
    }

    /**
     * Testing what a logged in user is not allowed todo
     * 
     * A user should not be able to edit articles she doesn’t own
     * A user should not be able to delete articles she doesn’t own
     * A user should not be able to create or update articles that bypass the validation rules (short titles and/or short bodies)
     */

    /**
     * A user should not be able to edit articles she doesn’t own
     * 
     * A user should not see the edit button on an article she doesn’t own
     * A user should not be able to visit the edit page of an article she doesn’t own
     * A user should not save edits on articles she doesn’t own
     */

     /**
      * A user should not see the edit button on an article she doesn’t own
      *
      *@test
      */

      function it_doesnt_show_edit_button_to_non_owners_of_article(){
        //1.Get a random user and one her articles
        $user = User::get()->random();
        // $user = factory(User::class)->create();
        //2.Get another user (different form the first one)
        $anotherUser =User::where('id', '<>', $user->id)->get()->random();
        // $article = $user->articles->random();
        $article = factory(Article::class)->create([
            'author_id' => $user->id,
        ]);
        //3.Act as the first user and visit the article and check that we are seeing the edit button
        $response = $this->actingAs($user)->get(route('view_article', ['id' => $article->id]));
        $response->assertSeeText('Edit Article');
        //4.Act as te second user and visit the article and check that we are not seeing the edit button
        $response = $this->actingAs($anotherUser)->get(route('view_article', ['id' => $article->id]));

        $response->assertDontSeeText('Edit Article');
      }

      /**
       * A user should not be able to visit the edit page of an article she doesn’t own
       * 
       * @test
       */

       public function it_preventes_non_owner_of_an_article_from_editing_it(){
        $user = User::get()->random();
        $anotherUser = User::where('id', '<>', $user->id)->first();

        $article = factory(Article::class)->create(['author_id' => $user->id]);

        // Non logged in user
        $response = $this->get(route('edit_article', ['id' => $article->id]));
        //Get a 403/forbidden response if not logged in
        $response->assertForbidden();

        //Logged in user
        $response = $this->actingAs($anotherUser)->get(route('edit_article', ['id' => $article->id]));
        $response->assertForbidden();
       }
       
       /**
        * A user could not save edits on articles she doesn’t own
        *
        *@test
        */

        public function it_preventes_non_owner_of_an_article_from_saving_edits(){
            $user = User::get()->random();
            $article = factory(Article::class)->create(['author_id' => $user->id]);

            $articleNewData = $this->getRandomArticleData();

            $anotherUser = User::where('id', '<>', $user->id)->first();

            $response = $this->actingAs($anotherUser)->json('POST', route('update_article',['id' => $article->id]), $articleNewData);

            $response->assertForbidden();

            $response = $this->json('POST', route('update_article', ['id' => $article->id]), $articleNewData);

            $response->assertForbidden();

        }

        /**
         * A user should not be able to delete articles she doesn't own
         * 
         * @test
         */

         public function it_preventes_non_owner_of_an_article_from_deleting_it()
         {
            $user = User::get()->random();
            $anotherUser = User::where('id', '<>', $user->id)->first();
            
            $article = factory(Article::class)->create(['author_id' => $user->id]);
            
            // non logged in user
            $response = $this->get(route('delete_article', ['id' => $article->id]));
            $response->assertForbidden();
            
            //logged in user
            $response = $this->actingAs($anotherUser)->get(route('delete_article', ['id' => $article->id]));
            $response->assertForbidden();
         }

         /**
          * A user should not be able to create or update articles that
          * bypass the validation rules (short titles and/or short bodies)
          *
          *@test
          */

         public function it_prevents_users_from_saving_articles_with_short_titles_and_bodies()
         {
         $user = User::get()->random();
         $article = factory(Article::class)->create(['author_id' => $user->id]);
         
         $articleNewData = [
          "title" => $this->faker->text(rand(5,9)),
          "body" => $this->faker->text(rand(5,9))
          ];
          
          $response = $this->actingAs($user)->json('POST', route('update_article', ['id' => $article->id]), $articleNewData);
          
          $response->assertJsonValidationErrors('title');
          $response->assertJsonValidationErrors('body');
          }


}

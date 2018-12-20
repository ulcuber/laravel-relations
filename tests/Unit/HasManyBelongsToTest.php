<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;

use App\Models\User;
use App\Models\Post;

class HasManyBelongsToTest extends TestCase
{
    public function testUserPostsWithImplicitKeys()
    {
        $users = User::with('posts')->get();
        $this->assertUserRelations($users);
    }

    public function testUserPostsWithExplicitKeys()
    {
        $users = (new class extends User {
            protected $table = 'users';
            public function posts()
            {
                return $this->hasMany(new class extends Post {
                    protected $table = 'posts';
                    public function user()
                    {
                        return $this->belongsTo(User::class, 'user_id', 'id');
                    }
                }, 'user_id', 'id');
            }
        })->with('posts')->get();
        $this->assertUserRelations($users);
    }

    public function testUserPostsWithExplicitRevertedKeys()
    {
        $users = (new class extends User {
            protected $table = 'users';
            public function posts()
            {
                return $this->hasMany(new class extends Post {
                    protected $table = 'posts';
                    public function user()
                    {
                        return $this->belongsTo(User::class, 'id', 'user_id');
                    }
                }, 'id', 'user_id');
            }
        })
        ->with('posts')->get();
        $this->assertUserRelationsNotFound($users);
    }

    public function testUserPostsWithExplicitKeysAndRevetedRelations()
    {
        $users = (new class extends User {
            protected $table = 'users';
            public function posts()
            {
                return $this->belongsTo(new class extends Post {
                    protected $table = 'posts';
                    public function user()
                    {
                        return $this->hasMany(User::class, 'user_id', 'id');
                    }
                }, 'user_id', 'id');
            }
        })
        ->with('posts')->get();
        $this->assertRevertedUserRelations($users);
    }

    public function testUserPostsWithExplicitRevertedKeysAndRevetedRelations()
    {
        $users = (new class extends User {
            protected $table = 'users';
            public function posts()
            {
                return $this->belongsTo(new class extends Post {
                    protected $table = 'posts';
                    public function user()
                    {
                        return $this->hasMany(User::class, 'id', 'user_id');
                    }
                }, 'id', 'user_id');
            }
        })
        ->with('posts')->get();
        $this->assertRevertedUserRelationsWithRevertedKeys($users);
    }

    private function assertUserRelations(Collection $users): void
    {
        $users->each(function (User $user) {
            $posts = $user->posts;
            $this->assertTrue(is_countable($posts));
            $this->assertTrue(count($posts) > 0);
            $posts->each(function (Post $post) use ($user) {
                $this->assertEquals($user->id, $post->user_id);
            });
        });
    }

    private function assertUserRelationsNotFound(Collection $users): void
    {
        $users->each(function (User $user) {
            $posts = $user->posts;
            $this->assertTrue(is_countable($posts));
            $this->assertFalse(count($posts) > 0);
        });
    }

    private function assertRevertedUserRelations(Collection $users): void
    {
        $users->each(function (User $user) {
            $posts = $user->posts;
            $this->assertNull($posts);
        });
    }

    private function assertRevertedUserRelationsWithRevertedKeys(Collection $users): void
    {
        $users->each(function (User $user) {
            $posts = $user->posts;
            $this->assertFalse(is_countable($posts));
            $this->assertEquals($user->id, $posts->user_id);
        });
    }
}

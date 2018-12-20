<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Collection;

use App\Models\User;
use App\Models\Post;

class BelongsToHasManyTest extends TestCase
{
    public function testPostUsersWithImplicitKeys()
    {
        $posts = Post::with('user')->get();
        $this->assertPostRelations($posts);
    }

    public function testPostUsersWithExplicitKeys()
    {
        $posts = (new class extends Post {
            protected $table = 'posts';
            public function user()
            {
                return $this->belongsTo(new class extends User {
                    protected $table = 'users';
                    public function post()
                    {
                        return $this->hasMany(Post::class, 'user_id', 'id');
                    }
                }, 'user_id', 'id');
            }
        })->with('user')->get();
        $this->assertPostRelations($posts);
    }

    public function testPostUsersWithExplicitRevertedKeys()
    {
        $this->expectException(QueryException::class);
        (new class extends Post {
            protected $table = 'posts';
            public function user()
            {
                return $this->belongsTo(new class extends User {
                    protected $table = 'users';
                    public function post()
                    {
                        return $this->hasMany(Post::class, 'id', 'user_id');
                    }
                }, 'id', 'user_id');
            }
        })
        ->with('user')->get();
    }

    public function testPostUsersWithExplicitRevertedKeysAndRevetedRelations()
    {
        $posts = (new class extends Post {
            protected $table = 'posts';
            public function user()
            {
                return $this->hasMany(new class extends User {
                    protected $table = 'users';
                    public function post()
                    {
                        return $this->belongsTo(User::class, 'id', 'user_id');
                    }
                }, 'id', 'user_id');
            }
        })
        ->with('user')->get();
        $this->assertRevertedPostRelations($posts);
    }

    private function assertPostRelations(Collection $posts): void
    {
        $posts->each(function (Post $post) {
            $user = $post->user;
            $this->assertTrue((bool) $user);
            $this->assertEquals($post->user_id, $user->id);
        });
    }

    private function assertRevertedPostRelations(Collection $posts): void
    {
        $posts->each(function (Post $post) {
            $users = $post->user;
            $this->assertTrue(is_countable($users));
            $this->assertEquals(1, count($users));
            $users->each(function (User $user) use ($post) {
                $this->assertEquals($post->user_id, $user->id);
            });
        });
    }
}

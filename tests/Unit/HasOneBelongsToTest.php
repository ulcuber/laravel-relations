<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;

use App\Models\User;
use App\Models\Phone;

class HasOneBelongsToTest extends TestCase
{
    public function testUserPhonesWithImplicitKeys()
    {
        $users = User::with('phone')->get();
        $this->assertUserRelations($users);
    }

    public function testUserPhonesWithExplicitKeys()
    {
        $users = (new class extends User {
            protected $table = 'users';
            public function phone()
            {
                return $this->hasOne(new class extends Phone {
                    protected $table = 'phones';
                    public function user()
                    {
                        return $this->belongsTo(User::class, 'user_id', 'id');
                    }
                }, 'user_id', 'id');
            }
        })->with('phone')->get();
        $this->assertUserRelations($users);
    }

    public function testUserPhonesWithExplicitRevertedKeys()
    {
        $users = (new class extends User {
            protected $table = 'users';
            public function phone()
            {
                return $this->hasOne(new class extends Phone {
                    protected $table = 'phones';
                    public function user()
                    {
                        return $this->belongsTo(User::class, 'id', 'user_id');
                    }
                }, 'id', 'user_id');
            }
        })
        ->with('phone')->get();
        $this->assertUserRelationsNotFound($users);
    }

    public function testUserPhonesWithExplicitKeysAndRevertedRelations()
    {
        $users = (new class extends User {
            protected $table = 'users';
            public function phone()
            {
                return $this->belongsTo(new class extends Phone {
                    protected $table = 'phones';
                    public function user()
                    {
                        return $this->hasOne(User::class, 'user_id', 'id');
                    }
                }, 'user_id', 'id');
            }
        })
        ->with('phone')->get();
        $this->assertUserRelationsNotFound($users);
    }

    public function testUserPhonesWithExplicitRevertedKeysAndRevertedRelations()
    {
        $users = (new class extends User {
            protected $table = 'users';
            public function phone()
            {
                return $this->belongsTo(new class extends Phone {
                    protected $table = 'phones';
                    public function user()
                    {
                        return $this->hasOne(User::class, 'id', 'user_id');
                    }
                }, 'id', 'user_id');
            }
        })
        ->with('phone')->get();
        $this->assertUserRelations($users);
    }

    private function assertUserRelations(Collection $users): void
    {
        $users->each(function (User $user) {
            $phone = $user->phone;
            $this->assertNotNull($phone);
            $this->assertEquals($user->id, $phone->user_id);
        });
    }

    private function assertUserRelationsNotFound(Collection $users): void
    {
        $users->each(function (User $user) {
            $phone = $user->phone;
            $this->assertNull($phone);
        });
    }
}

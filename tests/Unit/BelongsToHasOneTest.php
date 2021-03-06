<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Collection;

use App\Models\User;
use App\Models\Phone;

class BelongsToHasOneTest extends TestCase
{
    public function testPhoneUsersWithImplicitKeys()
    {
        $phones = Phone::with('user')->get();
        $this->assertPhoneRelations($phones);
    }

    public function testPhoneUsersWithExplicitKeys()
    {
        $phones = (new class extends Phone {
            protected $table = 'phones';
            public function user()
            {
                return $this->belongsTo(User::class, 'user_id', 'id');
            }
        })->with('user')->get();
        $this->assertPhoneRelations($phones);
    }

    public function testPhoneUsersWithExplicitRevertedKeys()
    {
        $this->expectException(QueryException::class);
        (new class extends Phone {
            protected $table = 'phones';
            public function user()
            {
                return $this->belongsTo(User::class, 'id', 'user_id');
            }
        })
        ->with('user')->get();
    }

    public function testPhoneUsersWithExplicitKeysAndRevetedRelations()
    {
        $this->expectException(QueryException::class);
        (new class extends Phone {
            protected $table = 'phones';
            public function user()
            {
                return $this->hasOne(User::class, 'user_id', 'id');
            }
        })
        ->with('user')->get();
    }

    public function testPhoneUsersWithExplicitRevertedKeysAndRevetedRelations()
    {
        $phones = (new class extends Phone {
            protected $table = 'phones';
            public function user()
            {
                return $this->hasOne(User::class, 'id', 'user_id');
            }
        })
        ->with('user')->get();
        $this->assertPhoneRelations($phones);
    }

    private function assertPhoneRelations(Collection $phones): void
    {
        $phones->each(function (Phone $phone) {
            $user = $phone->user;
            $this->assertNotNull($user);
            $this->assertEquals($phone->user_id, $user->id);
        });
    }
}

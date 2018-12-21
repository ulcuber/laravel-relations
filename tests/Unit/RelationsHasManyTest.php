<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

use App\Models\User;
use App\Models\Post;

class RelationsHasManyTest extends TestCase
{
    use DatabaseTransactions;

    const MAIN_COUNT = 5;
    const RELATED_COUNT = 3;

    public function provider()
    {
        return [
            [
                'user_id',
                [
                    User::class => Post::class,
                    'posts' => 'user',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testRelations(string $foreignKey, array $arr)
    {
        $keys = array_keys($arr);

        $main = $keys[0];
        $related = $arr[$main];

        if (isset($keys[1])) {
            $mainRelation = $keys[1];
            $relatedRelation = $arr[$mainRelation];
        } else {
            $mainRelation = str_plural(class_basename($related));
            $relatedRelation = class_basename($main);
        }

        $relatedModels = new EloquentCollection();
        $models = factory($main, static::MAIN_COUNT)->create();
        foreach ($models as $model) {
            $newRelated = factory($related, static::RELATED_COUNT)
                ->create([
                    $foreignKey => $model->id,
                ]);
            $relatedModels->merge($newRelated);
        }

        $this->assertHasMany($models, $foreignKey, $mainRelation);
        $this->assertBelongsTo($relatedModels, $foreignKey, $relatedRelation);
    }

    private function assertHasMany(
        EloquentCollection $models,
        string $foreignKey,
        string $relation
    ): void {
        $models->load($relation);
        foreach ($models as $model) {
            $related = $model->{$relation};
            $this->assertTrue(is_countable($related));
            $this->assertEquals(static::RELATED_COUNT, count($related));
            foreach ($related as $r) {
                $this->assertEquals($model->id, $r->{$foreignKey});
            }
        }
    }

    private function assertBelongsTo(
        EloquentCollection $models,
        string $foreignKey,
        string $relation
    ): void {
        $models->load($relation);
        foreach ($models as $model) {
            $main = $model->{$relation};
            $this->assertNotNull($main);
            $this->assertEquals($model->{$foreignKey}, $main->id);
        }
    }
}

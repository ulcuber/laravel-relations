# Test Laravel Relations

## Use
```bash
composer install
# env
php artisan key:generate
php artisan migrate --seed
vendor/bin/phpunit
```

# Result

## Designations

### Cases
* `doc` default behavior
* `keys` With Explicit Reverted Keys
* `rel` With Explicit Keys And Reverted Relations
* `kr` With Explicit Reverted Keys And Reverted Relations

### Results
* `ok` end result as documented
* `qe` Illuminate\\Database\\QueryException
* `null` relation not found
* `0` countable but empty
* `1` countable but only one
* `s` single not countable

### Entities
* `has` main entity
* `belongs` entity with FK field

## One to One
|         | doc | keys | rel  | kr |
|---------|-----|------|------|----|
| has     | ok  | null | null | ok |
| belongs | ok  | qe   | qe   | ok |

## One to Many
|         | doc | keys | rel  | kr |
|---------|-----|------|------|----|
| has     | ok  | 0    | null | s  |
| belongs | ok  | qe   | qe   | 1  |

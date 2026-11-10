<?php

declare(strict_types=1);

namespace app\support\Schema;

use app\model\Concerns\DefinesTableSchema;
use Illuminate\Database\Capsule\Manager as DB;
use ReflectionClass;

class ModelSchemaSynchronizer
{
    /** @var array<string, int> */
    private const SYNC_ORDER = [
        'AdminModel' => 10,
        'RoleModel' => 20,
        'PermissionModel' => 30,
        'CategoryModel' => 40,
        'ArticleModel' => 50,
        'SettingModel' => 60,
        'AdminLogModel' => 70,
        'AdminRoleModel' => 80,
        'RolePermissionModel' => 90,
    ];

    /**
     * @return array<int, array{class: class-string, schema: array}>
     */
    public function discover(?string $modelName = null): array
    {
        $models = [];
        $root = base_path('app/model');
        $files = glob($root . '/*.php') ?: [];
        $files = array_merge($files, glob($root . '/*/*.php') ?: []);

        $target = $modelName !== null ? self::normalizeModelClassName($modelName) : null;

        foreach ($files as $file) {
            $baseName = basename($file, '.php');
            if ($target !== null && strcasecmp($baseName, $target) !== 0) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($file, strlen($root) + 1));
            $class = 'app\\model\\' . str_replace('/', '\\', substr($relative, 0, -4));

            if (!class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            if ($reflection->isAbstract() || !$reflection->hasMethod('tableSchema')) {
                continue;
            }

            if (!in_array(DefinesTableSchema::class, class_uses($class), true)) {
                continue;
            }

            $schema = $class::tableSchema();
            $schema['table'] = $schema['table'] ?? $this->guessTableName($baseName);

            $models[] = [
                'class' => $class,
                'name' => $baseName,
                'schema' => $schema,
                'order' => self::SYNC_ORDER[$baseName] ?? 1000,
            ];
        }

        usort($models, static fn (array $a, array $b) => $a['order'] <=> $b['order']);

        return $models;
    }

    /**
     * @return array{created: string[], updated: string[], skipped: string[]}
     */
    public function sync(?string $modelName = null): array
    {
        $result = ['created' => [], 'updated' => [], 'skipped' => []];
        $models = $this->discover($modelName);

        if ($models === []) {
            return $result;
        }

        foreach ($models as $model) {
            $table = $model['schema']['table'];
            $schema = $model['schema'];

            if (!DB::schema()->hasTable($table)) {
                DB::schema()->create($table, function ($blueprint) use ($schema) {
                    SchemaBlueprintBuilder::apply($blueprint, $schema, true);
                });
                $result['created'][] = $table;
                continue;
            }

            $structureChanged = $this->tableNeedsUpdate($schema);
            $commentChanged = SchemaCommentSynchronizer::commentsNeedUpdate($schema);

            if ($structureChanged) {
                DB::schema()->table($table, function ($blueprint) use ($schema) {
                    SchemaBlueprintBuilder::apply($blueprint, $schema, false);
                });
            }

            if ($commentChanged) {
                SchemaCommentSynchronizer::sync($schema);
            }

            if ($structureChanged || $commentChanged) {
                $result['updated'][] = $table;
            } else {
                $result['skipped'][] = $table;
            }
        }

        return $result;
    }

    private function tableNeedsUpdate(array $schema): bool
    {
        $table = $schema['table'];
        $builder = DB::schema();

        foreach ($schema['columns'] ?? [] as $name => $definition) {
            if (!$builder->hasColumn($table, $name)) {
                return true;
            }
        }

        foreach ($schema['indexes'] ?? [] as $index) {
            $columns = $index['columns'] ?? [];
            if ($columns === []) {
                continue;
            }
            $name = $index['name'] ?? SchemaBlueprintBuilder::indexName($table, $columns);
            if (!$builder->hasIndex($table, $name)) {
                return true;
            }
        }

        foreach ($schema['unique'] ?? [] as $unique) {
            $columns = $unique['columns'] ?? [];
            if ($columns === []) {
                continue;
            }
            $name = $unique['name'] ?? SchemaBlueprintBuilder::uniqueName($table, $columns);
            if (!$builder->hasIndex($table, $name)) {
                return true;
            }
        }

        return false;
    }

    private function guessTableName(string $baseName): string
    {
        $name = str_ends_with($baseName, 'Model') ? substr($baseName, 0, -5) : $baseName;

        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name) ?? $name) . 's';
    }

    public static function normalizeModelClassName(string $name): string
    {
        $name = ucfirst($name);

        return str_ends_with($name, 'Model') ? $name : $name . 'Model';
    }
}

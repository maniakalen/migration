<?php
/**
 * Created by PhpStorm.
 * User: peter.georgiev
 * Date: 22/11/2018
 * Time: 10:33
 */

namespace maniakalen\migration\controllers;

use yii\db\Expression;
use yii\db\Query;
use yii\db\TableSchema;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\console\Exception;

class MigrateController extends \yii\console\controllers\MigrateController
{

    public $generatorTemplateFiles = [
        'create_table' => '@maniakalen/migration/views/createTableMigration.php',
        'drop_table' => '@maniakalen/migration/views/dropTableMigration.php',
        'add_column' => '@maniakalen/migration/views/addColumnMigration.php',
        'drop_column' => '@maniakalen/migration/views/dropColumnMigration.php',
        'create_junction' => '@maniakalen/migration/views/createTableMigration.php',
    ];

    public function init()
    {
        parent::init();
        \Yii::setAlias('@maniakalen/migration/views', dirname(__DIR__) . '/views');

    }

    /**
     * Generates migration file based on table structure (supports only MySQL ATM)
     *
     * @param $table
     * @throws \yii\console\Exception
     */
    public function actionGenerate($tableName)
    {

        $name = "create_{$tableName}_table";

        $schema = \Yii::$app->db->schema;

        /** @var TableSchema $table */
        $table = $schema->getTableSchema($tableName);
        $tableIndexes = $schema->getTableIndexes($tableName);
        if (!preg_match('/^[\w\\\\]+$/', $name)) {
            throw new Exception('The migration name should contain letters, digits, underscore and/or backslash characters only.');
        }
        $this->generateFieldsSignatures($table);
        list($namespace, $className) = $this->generateClassName($name);
        // Abort if name is too long
        $nameLimit = $this->getMigrationNameLimit();
        if ($nameLimit !== null && strlen($className) > $nameLimit) {
            throw new Exception('The migration name is too long.');
        }

        $migrationPath = $this->findMigrationPath($namespace);

        $file = $migrationPath . DIRECTORY_SEPARATOR . $className . '.php';
        if ($this->confirm("Create new migration '$file'?")) {
            $content = $this->generateMigrationSourceCode([
                'name' => $name,
                'className' => $className,
                'namespace' => $namespace,
                'indexes' => $tableIndexes,
                'data' => $this->getTableData($tableName)
            ]);
            FileHelper::createDirectory($migrationPath);
            file_put_contents($file, $content);
            $this->stdout("New migration created successfully.\n", Console::FG_GREEN);
        }
    }

    protected function getTableData($tableName)
    {
        return (new Query())->from($tableName)->createCommand()->queryAll(\PDO::FETCH_ASSOC);
    }


    /**
     * Generates class base name and namespace from migration name from user input.
     * @param string $name migration name from user input.
     * @return array list of 2 elements: 'namespace' and 'class base name'
     * @since 2.0.10
     */
    private function generateClassName($name)
    {
        $namespace = null;
        $name = trim($name, '\\');
        if (strpos($name, '\\') !== false) {
            $namespace = substr($name, 0, strrpos($name, '\\'));
            $name = substr($name, strrpos($name, '\\') + 1);
        } else {
            if ($this->migrationPath === null) {
                $migrationNamespaces = $this->migrationNamespaces;
                $namespace = array_shift($migrationNamespaces);
            }
        }

        if ($namespace === null) {
            $class = 'm' . gmdate('ymd_His') . '_' . $name;
        } else {
            $class = 'M' . gmdate('ymdHis') . ucfirst($name);
        }

        return [$namespace, $class];
    }

    protected function generateFieldsSignatures(TableSchema $table)
    {
        $fks = $table->foreignKeys;
        $fields = [];
        foreach ($table->columns as $column) {
            $field = $column->name;
            if ($column->type === 'timestamp') {
                if ($column->defaultValue && ($column->defaultValue instanceof Expression)) {
                    $field .= ":'timestamp DEFAULT {$column->defaultValue->expression}'";
                } else {
                    $field .= ":timestamp";
                }
            } else if (empty($column->enumValues)) {
                $field .= ":{$column->phpType}({$column->size})";
            } else {
                $field .= ":{$column->dbType}";
            }
            if (!$column->allowNull) {
                $field .= ':notNull';
            }
            if ($column->isPrimaryKey) {
                $field = "{$column->name}:primaryKey";
            }
            if ($column->defaultValue && $column->type !== 'timestamp') {
                $field .= ":defaultValue({$column->defaultValue})";
            }
            foreach ($fks as $fk) {
                if (isset($fk[$column->name])) {
                    $cname = $column->name;
                    $field .= ":foreignKey({$fk[0]} {$fk[$cname]})";
                }
            }
            $fields[] = $field;
        }

        if (!empty($fields)) {
            $this->fields = $fields;
        }
    }

    /**
     * Finds the file path for the specified migration namespace.
     * @param string|null $namespace migration namespace.
     * @return string migration file path.
     * @throws \Exception on failure.
     * @since 2.0.10
     */
    private function findMigrationPath($namespace)
    {
        if (empty($namespace)) {
            return is_array($this->migrationPath) ? reset($this->migrationPath) : $this->migrationPath;
        }

        if (!in_array($namespace, $this->migrationNamespaces, true)) {
            throw new Exception("Namespace '{$namespace}' not found in `migrationNamespaces`");
        }

        return $this->getNamespacePath($namespace);
    }

    /**
     * Returns the file path matching the give namespace.
     * @param string $namespace namespace.
     * @return string file path.
     * @since 2.0.10
     */
    private function getNamespacePath($namespace)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, \Yii::getAlias('@' . str_replace('\\', '/', $namespace)));
    }
}
<?php
/**
 * @link http://neamlabs.com/
 * @copyright Copyright (c) 2015 Neam AB
 */

namespace neam\gii2_file_registry_generators;

use yii\base\ErrorException;
use yii\helpers\Json;
use Yii;

abstract class MigrationGenerator extends \yii\gii\Generator
{

    public $ns;

    public $migrationPath;

    protected function attributesReferencingPrimaryKeyOfTable($table)
    {

        $sourceDb = Yii::$app->db;
        $sourceColumns = $sourceDb->createCommand(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema=:table_schema"
        )->bindValue(':table_schema', DATABASE_NAME)->queryAll();

        $attributes = [];
        foreach ($sourceColumns as $sourceColumn) {

            $attributeFkMetadata = $this->attributeFkMetadata(
                $sourceDb,
                $sourceColumn["TABLE_NAME"],
                $sourceColumn["COLUMN_NAME"]
            );

            if (!empty($attributeFkMetadata) && $attributeFkMetadata["REFERENCED_TABLE_NAME"] !== null && $attributeFkMetadata["REFERENCED_COLUMN_NAME"] === "id") {

                // Only those referencing primary key of $table
                if ($attributeFkMetadata["REFERENCED_TABLE_NAME"] !== $table) {
                    continue;
                }

                $sourceColumn["attributeFkMetadata"] = $attributeFkMetadata;
                $attributes[] = $sourceColumn;

            } else {
                continue;
            }

        }

        return $attributes;
    }

    protected function attributeFkMetadata($db, $table_name, $attribute)
    {
        $attributeFkMetadata = $db->createCommand(
            "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = :table_name AND COLUMN_NAME = :column_name"
        )->bindValue(':table_name', $table_name)
            ->bindValue(':column_name', $attribute)
            ->queryAll();

        if (empty($attributeFkMetadata)) {
            return [];
        }

        // Silly queryAll instead of query workaround
        $attributeFkMetadata = $attributeFkMetadata[0];

        $rules = $db->createCommand(
            "SELECT UPDATE_RULE, DELETE_RULE FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS WHERE TABLE_NAME = :table_name AND CONSTRAINT_NAME = :constraint_name"
        )->bindValue(':table_name', $table_name)
            ->bindValue(':constraint_name', $attributeFkMetadata["CONSTRAINT_NAME"])
            ->queryAll();

        // Silly queryAll instead of query workaround
        if (!isset($rules[0])) {
            $rules = [[]];
        }
        $attributeFkMetadata["rules"] = $rules[0];

        return $attributeFkMetadata;
    }

}
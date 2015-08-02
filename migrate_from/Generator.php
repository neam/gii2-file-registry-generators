<?php

namespace neam\gii2_file_registry_generators\migrate_from;

use Yii;
use yii\base\Exception;
use yii\gii\CodeFile;
use yii\helpers\Inflector;


/**
 * Yii Workflow UI Generator.
 * @author Fredrik Wollsén <fredrik@neam.se>
 * @since 1.0
 */
class Generator extends \neam\gii2_file_registry_generators\MigrationGenerator
{

    public $legacyMediaTableName = "p3_media";
    public $fileTableName = "file";

    /**
     * @inheritdoc
     */
    public $templates = [
        'p3media' => '@vendor/neam/gii2-file-registry-generators/migrate_from/p3media',
    ];

    public function getName()
    {
        return 'File Registry "Migrate From" Generator';
    }

    public function getDescription()
    {
        return 'Generates Yii 1 data and schema migrations for converting P3Media media attributes into File Registry ditos (Note: the data migration uses EDbMigration class form yiiext/migrate-command)';
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];

        // Detect attributes referencing the legacy table
        $legacyMediaAttributes = $this->attributesReferencingPrimaryKeyOfTable($this->legacyMediaTableName);
        if (empty($legacyMediaAttributes)) {
            throw new Exception("No legacy foreign keys found referencing " . $this->legacyMediaTableName);
        }

        foreach ($legacyMediaAttributes as $legacyAttribute) {
            $this->generateUpAndDownCommandsForLegacyAttribute($legacyAttribute);
        }
        $up = $this->up;
        $down = $this->down;

        $migrationPrefix = 'm' . gmdate('ymd_His');

        // Generate migration to copy legacy media data to file registry tables (without it, the fks can not be moved to point to the file table)
        $migrationName = $migrationPrefix . "_p3media_to_file_registry_data_migration";
        $files[] = new CodeFile(
            $this->migrationsPath() . $migrationName . ".php",
            $this->render(
                'p3media-to-file-registry-data-migration.php',
                compact("migrationName")
            )
        );

        // Generate migration to move foreign keys to file table
        $migrationName = $migrationPrefix . "_p3media_to_file_registry_schema_migration";
        $files[] = new CodeFile(
            $this->migrationsPath() . $migrationName . ".php",
            $this->render(
                'p3media-referencing-table-migration.php',
                compact("migrationName", "up", "down")
            )
        );

        return $files;
    }

    protected function migrationsPath()
    {
        return Yii::getAlias("@app/migrations/file-registry-migrate-from-p3media/");
    }

    protected $up = [];
    protected $down = [];

    protected function generateUpAndDownCommandsForLegacyAttribute($legacyAttribute)
    {
        $attributeFk = $legacyAttribute["attributeFkMetadata"];
        $sourceTable = $legacyAttribute["TABLE_NAME"];
        $attribute = $legacyAttribute["COLUMN_NAME"];
        $legacyFkName = $attributeFk["CONSTRAINT_NAME"];
        $newFkName = str_replace($this->legacyMediaTableName, $this->fileTableName, $legacyFkName);

        // Skip p3media's own tree_parent_id attribute
        if ($sourceTable === $this->legacyMediaTableName) {
            return;
        }

        // Remove existing fk
        $this->up[] = '$this->dropForeignKey(\'' . $legacyFkName
            . '\', \'' . $sourceTable . '\');';
        $this->down[] = '$this->dropForeignKey(\'' . $newFkName
            . '\', \'' . $sourceTable . '\');';

        // Add fk to proper table
        $this->up[] = '$this->addForeignKey(\'' . $newFkName // $name
            . '\', \'' . $sourceTable // $table
            . '\', \'' . $attribute // $columns
            . '\', \'' . $this->fileTableName // $refTable
            . '\', \'' . $attributeFk["REFERENCED_COLUMN_NAME"] // $refColumns
            . '\', \'' . $attributeFk["rules"]["DELETE_RULE"] // $delete
            . '\', \'' . $attributeFk["rules"]["UPDATE_RULE"] // $update
            . '\');';
        $this->down[] = '$this->addForeignKey(\'' . $legacyFkName
            . '\', \'' . $sourceTable // $table
            . '\', \'' . $attribute // $columns
            . '\', \'' . $this->legacyMediaTableName // $refTable
            . '\', \'' . $attributeFk["REFERENCED_COLUMN_NAME"] // $refColumns
            . '\', \'' . $attributeFk["rules"]["DELETE_RULE"]
            . '\', \'' . $attributeFk["rules"]["UPDATE_RULE"]
            . '\');';

    }

}

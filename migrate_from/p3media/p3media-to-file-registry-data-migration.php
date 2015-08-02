<?php
echo "<?php\n";
?>

class <?=$migrationName?> extends EDbMigration
{
	public function up()
	{

        $this->execute("REPLACE INTO `<?=$generator->fileTableName?>` (id, path, created, modified) SELECT id, path, created_at, updated_at FROM `<?=$generator->legacyMediaTableName?>`");
        $this->execute("REPLACE INTO `<?=$generator->fileTableName?>_instance` (id, file_id, storage_component_ref, created, modified) SELECT id, id, 'local', created_at, updated_at FROM `<?=$generator->legacyMediaTableName?>`");

	}

	public function down()
	{
		echo "<?=$migrationName?> does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}

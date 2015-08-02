<?php
echo "<?php\n";
?>

class <?=$migrationName?> extends EDbMigration
{
	public function up()
	{

<?php foreach ($up as $statement): ?>
        <?= $statement . "\n" ?>
<?php endforeach; ?>

	}

	public function down()
	{

<?php foreach ($down as $statement): ?>
        <?= $statement . "\n" ?>
<?php endforeach; ?>

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

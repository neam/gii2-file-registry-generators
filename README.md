gii2-file-registry-generators
=============================

File Registry "Migrate From" Generator
--------------------------------------

Generates Yii 1 data and schema migrations for converting P3Media media attributes into File Registry ditos (Note: the data migration uses EDbMigration class form yiiext/migrate-command)

Usage:

    tools/code-generator/yii gii/file-registry-migrate-from --template=p3media --overwrite=1 --interactive=0
    mv tools/code-generator/migrations/file-registry-migrate-from-p3media/*.php dna/db/migrations/common/

Links
-----

- [DNA Project Page](http://neamlabs.com/dna-project-base/)
- [GitHub](https://github.com/neam/gii2-file-registry-generators)

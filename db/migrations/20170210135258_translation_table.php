<?php

use Phinx\Migration\AbstractMigration;

class TranslationTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->table('translation')
            ->addColumn('language', 'string', ['limit' => 5, 'collation' => 'utf8_bin'])
            ->addColumn('key_name', 'string', ['limit' => 48, 'collation' => 'utf8_bin'])
            ->addColumn('value', 'text', ['collation' => 'utf8_bin'])
            ->addIndex(['language', 'key_name'], ['unique' => true])
            ->create();
    }
}

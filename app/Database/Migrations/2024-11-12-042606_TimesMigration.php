<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TimesMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
			'time_id'          => [
				'type'           => 'INT',
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'time_name'       => [
				'type'           => 'VARCHAR',
				'constraint'     => '255'
			],
			'time_start'       => [
				'type'           => 'TIME'
			],
			'time_end'       => [
				'type'           => 'TIME'
			],
			'color'       => [
				'type'           => 'VARCHAR',
				'constraint'     => '255'
			],
			'created_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
			'updated_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
			'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
		]);
		$this->forge->addKey('time_id', true);
		$this->forge->createTable('times');
    }

    public function down()
    {
        $this->forge->dropTable('times');
    }
}

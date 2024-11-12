<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PresenceMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
			'presence_id'          => [
				'type'           => 'INT',
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'worktime_id'       => [
				'type'           => 'INT',
				'constraint'     => '11',
                'unsigned'      => true
			],
			'employee_id'       => [
				'type'           => 'INT',
				'constraint'     => '11',
                'unsigned'      => true
			],
            'presensce_date'       => [
				'type'          => 'DATE',
			],
            'presence_type'       => [
				'type'           => 'VARCHAR',
				'constraint'     => '10',
                'default'       => 'in'
			],
			'created_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
			'updated_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
			'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
		]);
		$this->forge->addKey('time_id', true);
        $this->forge->addForeignKey('worktime_id','worktimes','worktime_id','CASCADE','CASCADE');
        $this->forge->addForeignKey('employee_id','employees','employee_id','CASCADE','CASCADE');
		$this->forge->createTable('presences');
    }

    public function down()
    {
        $this->forge->dropTable('presences');
    }
}

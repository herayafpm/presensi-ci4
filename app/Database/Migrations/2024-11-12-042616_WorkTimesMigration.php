<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WorkTimesMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
			'worktime_id'          => [
				'type'           => 'INT',
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'employee_id'       => [
				'type'           => 'INT',
				'constraint'     => '11',
                'unsigned'      => true
			],
			'time_id'       => [
				'type'           => 'INT',
				'constraint'     => '11',
                'unsigned'      => true,
				'null'		=> true
			],
            'year'       => [
				'type'           => 'INT',
				'constraint'     => '11',
			],
            'month'       => [
				'type'           => 'INT',
				'constraint'     => '11',
			],
            'day'       => [
				'type'           => 'INT',
				'constraint'     => '11',
			],
			'created_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
			'updated_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
			'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
		]);
		$this->forge->addKey('time_id', true);
        $this->forge->addForeignKey('employee_id','employees','employee_id','CASCADE','CASCADE');
        $this->forge->addForeignKey('time_id','times','time_id','CASCADE','CASCADE');
		$this->forge->createTable('times');
    }

    public function down()
    {
        $this->forge->dropTable('times');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EmployeesMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
			'employee_id'          => [
				'type'           => 'INT',
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'user_id'       => [
				'type'           => 'INT',
                'unsigned'        => true
			],
			'gender'       => [
				'type'           => 'VARCHAR',
				'constraint'     => '1',
                'default'       => 'L'
			],
			'datebirth'       => [
				'type'           => 'VARCHAR',
				'constraint'     => '255',
                'null'          => true
			],
			'placebirth'       => [
				'type'           => 'VARCHAR',
				'constraint'     => '255',
                'null'          => true
			],
			'created_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
			'updated_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
			'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
		]);
		$this->forge->addKey('employee_id', true);
		$this->forge->addForeignKey('user_id','users','user_id','CASCADE','CASCADE');
		$this->forge->createTable('employees');
    }

    public function down()
    {
        $this->forge->dropTable('employees');
    }
}

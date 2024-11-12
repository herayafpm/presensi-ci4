<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserHasRoleMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
			'user_has_role_id'          => [
				'type'           => 'INT',
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'user_id'          => [
				'type'           => 'INT',
				'unsigned'       => true
			],
			'role_id'          => [
				'type'           => 'INT',
				'unsigned'       => true
			],
			'created_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')]
		]);
		$this->forge->addKey('user_has_role_id', true);
        $this->forge->addForeignKey('user_id','users','user_id','CASCADE','CASCADE');
        $this->forge->addForeignKey('role_id','roles','role_id','CASCADE','CASCADE');
		$this->forge->createTable('user_has_roles');
    }

    public function down()
    {
        $this->forge->dropTable('user_has_roles');
    }
}

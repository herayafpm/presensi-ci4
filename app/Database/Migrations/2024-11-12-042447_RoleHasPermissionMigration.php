<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RoleHasPermissionMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
			'role_has_permission_id'          => [
				'type'           => 'INT',
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'role_id'          => [
				'type'           => 'INT',
				'unsigned'       => true
			],
			'permission_id'          => [
				'type'           => 'INT',
				'unsigned'       => true
			],
			'created_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')]
		]);
		$this->forge->addKey('role_has_permission_id', true);
        $this->forge->addForeignKey('role_id','roles','role_id','CASCADE','CASCADE');
        $this->forge->addForeignKey('permission_id','permissions','permission_id','CASCADE','CASCADE');
		$this->forge->createTable('role_has_permissions');
    }

    public function down()
    {
        $this->forge->dropTable('role_has_permissions');
    }
}

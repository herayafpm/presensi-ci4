<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserHasPermissionMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
			'user_has_permission_id'          => [
				'type'           => 'INT',
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'user_id'          => [
				'type'           => 'INT',
				'unsigned'       => true
			],
			'permission_id'          => [
				'type'           => 'INT',
				'unsigned'       => true
			],
			'is_denied'          => [
				'type'           => 'INT',
				'constraint'	=> '1',
				'default'		=> 0
			],
			'created_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')]
		]);
		$this->forge->addKey('user_has_permission_id', true);
        $this->forge->addForeignKey('user_id','users','user_id','CASCADE','CASCADE');
        $this->forge->addForeignKey('permission_id','permissions','permission_id','CASCADE','CASCADE');
		$this->forge->createTable('user_has_permissions');
    }

    public function down()
    {
        $this->forge->dropTable('user_has_permissions');
    }
}

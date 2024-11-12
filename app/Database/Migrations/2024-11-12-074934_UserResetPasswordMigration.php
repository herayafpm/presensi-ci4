<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserResetPasswordMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
			'user_reset_password_id'          => [
				'type'           => 'INT',
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'user_id'          => [
				'type'           => 'INT',
				'unsigned'       => true
			],
			'email'          => [
				'type'           => 'VARCHAR',
				'constraint'     => '255',
			],
			'code'          => [
				'type'           => 'VARCHAR',
				'constraint'	=> '6'
			],
			'sended'          => [
				'type'           => 'INT',
				'constraint'	=> '1',
				'default'		=> 0
			],
			'created_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')]
		]);
		$this->forge->addKey('user_reset_password_id', true);
        $this->forge->addForeignKey('user_id','users','user_id','CASCADE','CASCADE');
		$this->forge->createTable('user_reset_passwords');
    }

    public function down()
    {
        $this->forge->dropTable('user_reset_passwords');
    }
}

<?php
require_once(dirname(__FILE__) . '../../../active_php/base.php');

require_once(dirname(__FILE__) . '/config.php');

 class UserMigration extends Migration {
	
	public function up() {
		$this->down();
		$user = $this->create_table('users');
			$user->string('name');
			$user->boolean('is_admin');
			$user->text('profile');
			$user->text('avatar');
		$user->go();
		
		$this->rename_column('users', 'name', 'name2');
	}
	
	public function down() {
		$this->drop_table('users');
	}
	
	
 }

	$m = new UserMigration();
	$m->run();

?>
<?php
require_once(dirname(__FILE__) . '../../../active_php/base.php');

require_once(dirname(__FILE__) . '/config.php');

 class PhotoMigration extends Migration {
	
	public function up() {
		$photo = $this->create_table('photos');
			$photo->string('caption');
			$photo->references('user');

		$photo->go();
	}
	
	public function down() {
		$this->drop_table('users');
	}
	
	
 }

	$m = new PhotoMigration();
	$m->run();

?>
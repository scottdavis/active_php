<?php	
	class TestMigration extends Migration {
		public $tables = array("users", "photos");
		
		public function up() {
				$table = $this->create_table('users');
					$table->string('name');
					$table->integer('my_int');
					$table->timestamps();
				$table->go();
				
				$table2 = $this->create_table('photos');
					$table2->belongs_to('user');
					$table2->string('title');
				$table2->go();
			}

			public function down() {
				foreach($this->tables as $t) {
					$this->drop_table($t);
				}
			}
		
		
		
	}
?>
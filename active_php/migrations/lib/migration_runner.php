<?php

	class MigrationRunner {
		
		static $dir = 'test';
		
		public static function migration_table_name() {
			return 'migrations';
		}
		
		public static function get_all_versions() {
			$table = ActivePhp\Base::$adapter->quote_table_name(self::migration_table_name());
			$results = ActivePhp\Base::$adapter->query('SELECT version FROM ' . $table . ' ORDER BY version ASC');
			$versions = array();
			while($row = $results->fetch_assoc()) {
				$versions[$row['version']] = $row['version'];
			}
			ksort($versions);
			return $versions;
		}
		
		public static function current_version() {
			$table = self::migration_table_name();
			if(ActivePhp\Base::$adapter->table_exists($table)) {
				$versions = self::get_all_versions();
				$version = self::get_max_version($versions);
			}else{
				$version = 0;
			}
			return (int) $version;
		}
		
		public static function create_version($version) {
			$table = ActivePhp\Base::$adapter->quote_table_name(self::migration_table_name());
			$sql = "INSERT INTO {$table} (version) VALUES ($version)";
			ActivePhp\Base::$adapter->query($sql);
		}
		
		public static function delete_version($version) {
			$table = ActivePhp\Base::$adapter->quote_table_name(self::migration_table_name());
			$sql = "DELETE FROM {$table} WHERE version = '$version'";
			ActivePhp\Base::$adapter->query($sql);
		}
		
		
		public function migrate($target_version = null) {
			if(is_null($target_version)) {
				self::up($target_version);
			}elseif((int) self::current_version() > (int) $target_version) {
				self::down($target_version);
			}else{
				self::up($target_version);
			}
		}
		
		public static function setup_table() {
			if(!ActivePhp\Base::$adapter->table_exists(self::migration_table_name())) {
				self::create_migration_table();
			}
		}
		
		public function up($to_version = NULL) {
			self::drop_migration_table();
			self::setup_table();
			$table = self::migration_table_name();
			$data = self::load_files(self::$dir);
			foreach($data as $version => $class) {
				if(!is_null($to_version) && (int) $version > (int) $to_version) {continue;}
				$klass = new $class();
				$klass->up();
				self::create_version($version);
			}
		}
		
	public static function down($to_version) {
		self::setup_table();
		$table = self::migration_table_name();
		$data = self::load_files(self::$dir);
		$data = array_reverse($data, true);
		foreach($data as $version => $class) {
			if((int) $version <= (int) $to_version) {continue;}
			$klass = new $class();
			$klass->down();
			self::delete_version($version);
		}
	}
	
	public static function drop_migration_table() {
		Migration::$show_sql = false;
		$mig = new Migration();
		$mig->drop_table(self::migration_table_name());
	}
	
	public static function create_migration_table() {
		Migration::$show_sql = false;
		$table = self::migration_table_name();
		$mig = new Migration();
		$t = $mig->create_table($table);
			$t->string('version');
		$t->go();
	}
	
	public static function load_files() {
		$dir = self::$dir;
		$curent_version = self::current_version();
		$classes = array();
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(preg_match('/\.php$/' , $file)) {
					$temp_array = preg_match('/^([0-9]+)_(.+)\.php/', $file, $matches);
					$version = $matches[1];
					if($version < $current_version) {
						continue;
					}
					$class_name = $matches[2];
					$classes[$version] = Inflector::classify($class_name);
					require_once($dir . '/' . $file);
				}
			}
			closedir($dh);
		}
		return $classes;
	}
	
	public static function get_max_version($array) {
		ksort($array);
		end($array);
		$key = key($array);
		return $key;
	}
		
	}

?>
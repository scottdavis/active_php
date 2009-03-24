<?php
namespace ActivePhp;

interface ConnectionAdapter {
	public function connect()
	public function query()
	public function escape()
	public function close()
	public function free()
	public function insert_id()
	public function fetch_assoc()
	public function fetch_all()
	public function fetch_row()	
}



     
<?php
require_once '../base.php';
require_once './models/user.php';
require_once './models/photo.php';

ActivePhp\Base::establish_connection(array(
  'host' => 'localhost',
  'database' => 'test_php',
  'username' => 'root',
  'password' => ''
));


$rc = new Activephp\ResultCollection(Photo::find_all());
echo "<br />";
echo $rc->to_xml();



$users = User::find_all();
$user = $users[0];
echo '<pre>';
print_r($users);
var_dump($user);
echo 'photos<br/>';

foreach($user->photos() as $photo){
	
	print_r($photo);
	echo "id<p>";
	echo $photo->id();
	echo "<p> ".$photo->file()." </p>";
}

echo 'end photos<br/>';

$photos2 = Photo::find(array(1,2));
var_dump($photos2);
echo "USer start<br/>";
var_dump($photos2[0]->user());
echo "user end<br/>";
$user2 = User::find($user->id());
var_dump($user2);
var_dump($user2->photos());
try{
	$user3 = User::find(4);
	var_dump($user3);
}catch(\ActivePhp\RecordNotFound $e){
	echo 'execption caught<br/>';
	echo'<br/>';
}

echo "Photo count<br/>";
echo Photo::count();


echo "<br/>";
echo Photo::count(array('column' => '*', 'conditions' => array('file' => '3')));
Photo::count(array('column' => '*', 'conditions' => array('file' => '3')));
echo "<br/>Photo count end<br/>";
echo Photo::min(array('column' => 'id'));
echo "<br/>Sum start<br/>";
echo Photo::sum(array('column' => 'id'));
echo "<br/>Sum stop<br/>";



$users2 = User::find_all();

print_r(User::create());
print_r(User::create());




print_r(ActivePhp\Base::$query_log);
echo '</pre>';




?>

<p><?php echo ActiveSupport\DateHelper::to_string('db', time()) ?></p>
<p><?php echo ActiveSupport\DateHelper::to_string('rfc822', time()) ?></p>
<p><?php echo ActiveSupport\TimeHelper::to_string('db', time()) ?></p>
<p><?php echo ActiveSupport\TimeHelper::to_string('rfc822', time()) ?></p>

<p>Memory usage: <?php echo xdebug_memory_usage()/1024/1024 ?></p>
<p>Peak Memory usage: <?php echo xdebug_peak_memory_usage()/1024/1024 ?></p>
<p>Execution Time: <?php echo xdebug_time_index() ?></p>

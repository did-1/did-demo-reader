<?php

require __DIR__ . '/vendor/autoload.php';
use Leaf\Blade;

$blade = new Blade('views', 'views/cache');

// db()->autoConnect(); should work from .env
db()->connect(['dbtype' => 'sqlite', 'dbname' => '../instance-nodejs/did.db']);

app()->get('/', function () {
	response()->page('./welcome.html');
});

app()->get('/posts', function () {
	global $blade;
	$posts = db()->select('posts')->all();
	echo $blade->make('posts', ['posts' => $posts])->render();
});

app()->run();

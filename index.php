<?php

require __DIR__ . '/vendor/autoload.php';
use Leaf\Blade;
use Leaf\Fetch;
//use function Leaf\fetch;

$blade = new Blade('views', 'views/cache');

// db()->autoConnect(); should work from .env
db()->connect(['dbtype' => 'sqlite', 'dbname' => '../instance-nodejs/did.db']);

app()->get('/', function () {
	response()->page('./welcome.html');
});

app()->get('/posts', function () {
	global $blade;

	$posts = db()->select('posts')->all();
	foreach ($posts as $post) {
		$url = "http://" . $post["path"];
		$res = Fetch::request([
			"method" => "GET",
			"url" => "http://" . $post["path"],
			"rawResponse" => true,
		]);
		// $res = fetch("http://" + $post["domain"] + "/" + $post["path"]);
		var_dump($res);
		die();
	}
	echo $blade->make('posts', ['posts' => $posts])->render();
});

app()->run();

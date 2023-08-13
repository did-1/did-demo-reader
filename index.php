<?php

require __DIR__ . '/vendor/autoload.php';

use Leaf\Blade;
use Leaf\Fetch;
//use function Leaf\fetch;

$blade = new Blade('views', 'views/cache');
$db = new App\Db();

app()->get('/', function () {
  response()->page('./welcome.html');
});

function fetchPost($url)
{
  $postContent = "";
  try {
    $res = Fetch::request([
      "method" => "GET",
      "url" => $url,
      "rawResponse" => true,
    ]);
    $doc = new DOMDocument();
    $doc->loadHTML($res->data);
    $metas = $doc->getElementsByTagName('meta');
    // $res = fetch("http://" + $post["domain"] + "/" + $post["path"]);
    $post["content"] = "YES";
    foreach ($metas as $meta) {
      $name = $meta->getAttribute('name');
      $content = $meta->getAttribute('content');

      if ($name === "did:content") {
        $postContent .= $content;
      }
    }
  } catch (Exception $e) {
  }
  return $postContent . "--DATA";
}

app()->get('/posts', function () {
  global $blade, $db;
  $posts = $db->getPosts();
  foreach ($posts as &$post) {
    $url = "http://" . $post["path"];
    // try to read from DB
    // if not existing in DB cache fetch from url
    $content = fetchPost($url);
    $post['content'] = $content;
  }
  echo $blade->make('posts', ['posts' => $posts])->render();
});

app()->run();

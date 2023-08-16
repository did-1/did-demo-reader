<?php

require __DIR__ . '/vendor/autoload.php';

try {
  \Dotenv\Dotenv::createUnsafeImmutable(__DIR__)->load();
} catch (\Throwable $th) {
  trigger_error($th);
}

use Leaf\Blade;
use Leaf\Fetch;
//use function Leaf\fetch;

$blade = new Blade('views', 'views/cache');

app()->get('/', function () {
  response()->page('./welcome.html');
});

function fetchPost($url)
{
  $postContent = [];
  try {
    $res = Fetch::request([
      "method" => "GET",
      "url" => $url,
      "rawResponse" => true,
    ]);
    $doc = new DOMDocument();
    $doc->loadHTML($res->data);
    $metas = $doc->getElementsByTagName('meta');
    foreach ($metas as $meta) {
      $name = $meta->getAttribute('name');
      $content = $meta->getAttribute('content');

      if ($name === "did:content") {
        $postContent[] = $content;
      }
    }
  } catch (Exception $e) {
  }
  return $postContent;
}

app()->get('/avatar', function () {
  // Set max-age to a week to benefit from client caching (this is optional)
  header('Cache-Control: max-age=604800');

  // Parse query string parameters
  $value = request()->get('value');
  $size = 32;

  // Render icon
  $icon = new \Jdenticon\Identicon();
  $style = new \Jdenticon\IdenticonStyle();
  $style->setPadding(0);
  // $icon->configure(['padding' => 0]);
  $icon->setStyle($style);
  $icon->setValue($value);
  $icon->setSize($size);
  $icon->displayImage('png');
});

app()->get('/posts', function () {
  global $blade;
  $db = new App\Db();
  $posts = $db->getPosts();
  foreach ($posts as &$post) {
    $url = "http://" . $post["path"];
    // try to read from DB
    // if not existing in DB cache fetch from url
    $content = fetchPost($url);
    $post['content'] = $content;
    $post['owner'] = explode("/", $post["path"])[0];
    $post['url'] = $url;
  }
  echo $blade->make('posts', ['posts' => $posts])->render();
});

app()->run();

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

function convertUrlsToLinks($text)
{
  $pattern = '/(http:\/\/|https:\/\/|www\.)[a-z0-9\-_]+\.[a-z0-9\-_]+(\/[a-zA-Z0-9#\-_\/\?&%=]*)?/';

  return preg_replace_callback($pattern, function ($matches) {
    return '<a href="' . e($matches[0]) . '" target="_blank">' . e($matches[0]) . '</a>';
  }, e($text));
}

app()->get('/', function () {
  response()->redirect('/posts');
});

function extractContent($data)
{
  $doc = new DOMDocument();
  $doc->loadHTML($data);
  $metas = $doc->getElementsByTagName('meta');
  foreach ($metas as $meta) {
    $name = $meta->getAttribute('name');
    $content = $meta->getAttribute('content');

    if ($name === "did:content") {
      $postContent[] = $content;
    }
  }
  return $postContent;
}

function isICOString($data)
{
  $header = substr($data, 0, 4);  // Extract the first 4 bytes
  return $header === "\x00\x00\x01\x00";
}

function fetchPost($path)
{
  $url = "http://" . $path;
  $postContent = [];
  try {
    $res = Fetch::request([
      "method" => "GET",
      "url" => $url,
      "rawResponse" => true,
    ]);
    $postContent = extractContent($res->data);
    $db = new App\Db();
    $db->savePostContent($path, $res->data);
  } catch (Exception $e) {
  }
  return $postContent;
}

app()->get('/avatar', function () {
  // Set max-age to a week to benefit from client caching (this is optional)
  header('Cache-Control: max-age=604800');

  $domain = request()->get('value');

  try {
    $res = Fetch::request([
      "method" => "GET",
      "url" => "http://" . $domain . "/favicon.ico",
      "rawResponse" => true,
    ]);
    if ($res->data && isICOString($res->data)) {
      header('Content-Type: image/x-icon');
      echo $res->data;
      return;
    }
  } catch (Exception $e) {
  }

  // Parse query string parameters
  $value = request()->get('value');
  $size = 64;

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
    $dbPost = $db->getPostContent($post["path"]);
    $content = "";
    if ($dbPost) {
      $content = extractContent($dbPost["content"]);
    } else {
      // TODO: move DB save and estractContent here
      $content = fetchPost($post["path"]);
    }
    $post['content'] = $content;
    $post['owner'] = explode("/", $post["path"])[0];
    $post['url'] = $url;
  }
  echo $blade->make('posts', ['posts' => $posts])->render();
});

app()->config('debug', true);
app()->run();

<?php

namespace App;

use Leaf\Db as LeafDb;

class Db
{
  protected LeafDb $readerDb;
  protected LeafDb $didDb;
  public function __construct()
  {
    $this->readerDb = new LeafDb();
    $this->readerDb->connect('', getenv('READER_DATABASE'), '', '', 'sqlite');
    $this->didDb = new LeafDb();
    $this->didDb->connect('', getenv('DID_DATABASE'), '', '', 'sqlite');
    // db()->connect(['dbtype' => 'sqlite', 'dbname' => '../instance-nodejs/did.db']);
    return $this->readerDb->query("CREATE TABLE IF NOT EXISTS contents (
      url TEXT PRIMARY KEY,
      content TEXT NOT NULL,
      scraped_at INTEGER NOT NULL
    );")->execute();
  }

  public function getPosts()
  {
    return $this->didDb->query(
      "SELECT p1.*, blocks.*, (SELECT COUNT(path) FROM posts p2 where p2.path = p1.path) as total FROM posts p1
        LEFT JOIN blocks ON p1.block = blocks.hash
        WHERE p1.path LIKE p1.domain || '%' 
        ORDER BY blocks.time DESC, p1.inserted_at DESC LIMIT 500"
      )->fetchAll();
  }

  public function savePostContent($url, $content)
  {
    return $this->readerDb
      ->insert("contents")
      ->params(["url" => $url, "content" => $content, "scraped_at" => time() * 1000])->execute();
  }

  public function getPostContent($url)
  {
    return $this->readerDb->query("SELECT * FROM contents WHERE url = ?")->bind($url)->fetchAssoc();
  }
}

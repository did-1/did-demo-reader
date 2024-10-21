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
    $this->readerDb->query("CREATE TABLE IF NOT EXISTS contents (
      url TEXT PRIMARY KEY,
      content TEXT NOT NULL,
      scraped_at INTEGER NOT NULL
    );")->execute();
    $this->readerDb->query("CREATE TABLE IF NOT EXISTS users (
      domain TEXT PRIMARY KEY,
      favicon BLOB NULL,
      scraped_at INTEGER NOT NULL
    );")->execute();
    return;
  }

  public function getPosts()
  {
    return $this->didDb->query(
        "SELECT p1.path, (SELECT COUNT(path) FROM posts p2 where p2.path = p1.path) as total,
	MIN (blocks.time) as time,
	MIN (p1.inserted_at) as inserted_at
        FROM posts p1
        LEFT JOIN blocks ON p1.block = blocks.hash
	GROUP BY p1.path
        ORDER BY time DESC, inserted_at DESC LIMIT 500"
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

  public function getAvatarByDomain(string $domain)
  {
    $result = $this->readerDb->query("SELECT favicon FROM users WHERE domain = ?")->bind($domain)->fetchAssoc();
    return $result;
  }

  public function saveAvatarForDomain(string $domain, string $avatarData): bool
  {
    return $this->readerDb
      ->insert("users")
      ->params(["domain" => $domain, "favicon" => $avatarData, "scraped_at" => time() * 1000])->execute();
    return true;
  }
}

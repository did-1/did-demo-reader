<?php

namespace App;

db()->autoConnect();

class Db
{
  public function __construct()
  {
    // db()->connect(['dbtype' => 'sqlite', 'dbname' => '../instance-nodejs/did.db']);
    db()->query("CREATE TABLE IF NOT EXISTS contents (
      url TEXT PRIMARY KEY,
      content TEXT NOT NULL,
      scraped_at INTEGER NOT NULL
    );")->execute();
  }

  public function getPosts()
  {
    return db()
      ->select("posts", "COUNT(path) AS total, path")
      ->groupBy("path")
      ->orderBy("inserted_at", "desc")
      ->limit(500)
      ->fetchAll();
  }

  public function savePostContent($url, $content)
  {
    return db()
      ->insert("contents")
      ->params(["url" => $url, "content" => $content, "scraped_at" => time() * 1000])->execute();
  }

  public function getPostContent($url)
  {
    return db()->query("SELECT * FROM contents WHERE url = ?")->bind($url)->fetchAssoc();
  }
}

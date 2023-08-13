<?php

namespace App;

// db()->autoConnect(); should work from .env

class Db
{
  public function __construct()
  {
    db()->connect(['dbtype' => 'sqlite', 'dbname' => '../instance-nodejs/did.db']);
    db()->query("CREATE TABLE IF NOT EXISTS contents (
      url TEXT PRIMARY KEY,
      content TEXT NOT NULL,
      scraped_at INTEGER NOT NULL
    );")->execute();
  }
  public function getPosts()
  {
    return db()->select('posts')->all();
  }
}


//db()->insert("users")->params(["username" => "mychi"])->execute();
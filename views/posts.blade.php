<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Posts</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <div class="container">
    @foreach ($posts as $post)
    <div class="post">
      <div class="avatar">
        <img src="/avatar/?value={{$post['owner']}}" alt="User Avatar">
      </div>
      <div class="content">
        <h3 class="username">{{$post['owner']}}</h3>
        @foreach ($post['content'] as $message)
        <p class="message">{!! convertUrlsToLinks($message) !!}</p>
        @endforeach
        <div class="interactions">
          <span class="retweets">
            <span class="retweet-icon">🔁</span>
            <span class="retweet-count">{{$post['total']}}</span>
          </span>
          <!-- <span class="comments">
            <span class="comment-icon">💬</span>
            <span class="comment-count">12</span>
          </span> -->
          <span class="post-link">
            <a href="{{$post['url']}}" class="post-link-icon"  target="_blank">🔗</a>
          </span>
          <span class="post-time">
            <span class="post-time-text">{{ \Carbon\Carbon::parse($post['time'])->format('Y-m-d H:i:s') }}</span>
            <span class="post-time-icon">🕒</span>
          </span>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  <div class="container">
    <!-- Add more post blocks as necessary -->
  </div>
</body>

</html>

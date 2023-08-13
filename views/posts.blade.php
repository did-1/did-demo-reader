<!Doctype html>
<html>

<head>
  <title>All Posts</title>
</head>

<body>
  <div class="container">
    @foreach ($posts as $post)
    <p>{{ $post['path'] }} - {{ $post['content'] }}</p>
    @endforeach
  </div>
</body>

</html>
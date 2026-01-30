<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Search Toots</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="Search a local database of toots">
  <meta name="author" content="John Johnston">
<style>
body {
    background-color: #f0f0f0;
    font-size: 18px;
    font-family: system-ui, sans-serif;
}

main {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: white;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
	 
}

.boost{background-color: #f0f0f0; padding: 10px;}
.invisible{display:none;}  


  </style> 
</head>

<body>
 
<main>
	<h1>Toot Search</h1>
	
	<form action="" method="get" accept-charset="utf-8">
		
	<label for="q">Search Toots:</label><input type="text" name="q" value="" id="q">
		<p><input type="submit" value="Continue &rarr;"></p>
	</form>


<?php
$db = new SQLite3('mastodon_posts.db');

// Search posts
$search = $_GET['q'] ?? '';

// Count results
$count_stmt = $db->prepare('SELECT COUNT(*) as total FROM posts WHERE content LIKE :search');
$count_stmt->bindValue(':search', '%' . $search . '%', SQLITE3_TEXT);
$count_result = $count_stmt->execute();
$total = $count_result->fetchArray(SQLITE3_ASSOC)['total'];

echo "<p>Found: " . $total . " toots</p>";

// Get results
$stmt = $db->prepare('SELECT * FROM posts WHERE content LIKE :search ORDER BY created_at DESC');
$stmt->bindValue(':search', '%' . $search . '%', SQLITE3_TEXT);
$results = $stmt->execute();

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $data = json_decode($row['data'], true);
	
	$date = new DateTime($row['created_at']);
	    $formatted_date = $date->format('d M Y, H:i'); // e.g., "28 Jan 2026, 14:30"
    
    // Check if it's a boost
    if (isset($data['reblog']) && $data['reblog']) {
        echo "<div class='boost'>";
        // Show media from the boosted post
        if (!empty($data['reblog']['media_attachments'])) {
            foreach ($data['reblog']['media_attachments'] as $media) {
                echo "<img src='" . htmlspecialchars($media['url']) . "' style='max-width:200px'>";
            }
        }
        echo "<p><em>🔁 Boosted from @" . htmlspecialchars($data['reblog']['account']['acct']) . ":</em></p>";
        echo "<div>" . $data['reblog']['content'] . "</div>";
        echo "<small><a href='" . htmlspecialchars($data['reblog']['url']) . "'>" .  $formatted_date . "</a></small>";
       
        
      
        echo "</div><hr>";
    } else {
        // Original post
        echo "<div>";
        // Decode media URLs
        $media = json_decode($row['media_urls'], true);
        if ($media) {
            foreach ($media as $url) {
                echo "<img src='" . htmlspecialchars($url) . "' style='max-width:200px'>";
            }
        }
        echo "<div>" . $row['content'] . "</div>";
        echo "<small><a href='" . htmlspecialchars($row['url']) . "'>" .  $formatted_date . "</a></small>";
        
      
        echo "</div><hr>";
    }
}
?></main></body>
</html>

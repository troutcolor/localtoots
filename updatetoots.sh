#!/bin/bash

source "$(dirname "$0")/config.sh"

since_id=$(sqlite3 "$DB" "SELECT MAX(id) FROM posts;")

if [ -z "$since_id" ]; then
    url="${BASE_URL}/api/v1/accounts/${USER_ID}/statuses?limit=40"
else
    url="${BASE_URL}/api/v1/accounts/${USER_ID}/statuses?limit=40&since_id=${since_id}"
fi

response=$(curl -s "$url")

count=$(echo "$response" | jq '. | length')

if [ "$count" -gt 0 ]; then
    echo "$response" | jq -r '.[] | @json' | while read -r post; do
        id=$(echo "$post" | jq -r '.id')
        created_at=$(echo "$post" | jq -r '.created_at')
        content=$(echo "$post" | jq -r '.content')
        post_url=$(echo "$post" | jq -r '.url')
        media_urls=$(echo "$post" | jq -c '[.media_attachments[].url]')
        data=$(echo "$post" | jq -c '.')
        
        sqlite3 "$DB" <<SQL
INSERT OR REPLACE INTO posts (id, created_at, content, url, media_urls, data)
VALUES ('$id', '$created_at', '$(echo "$content" | sed "s/'/''/g")', '$post_url', '$media_urls', '$(echo "$data" | sed "s/'/''/g")');
SQL
    done
    echo "Added $count new posts"
else
    echo "No new posts"
fi
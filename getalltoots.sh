#!/bin/bash

source "$(dirname "$0")/config.sh"

# Initialize database
sqlite3 "$DB" <<EOF
CREATE TABLE IF NOT EXISTS posts (
    id TEXT PRIMARY KEY,
    created_at TEXT,
    content TEXT,
    url TEXT,
    media_urls TEXT,
    data JSON
);
EOF

# Fetch all posts
fetch_all_posts() {
    local max_id=""
    local count=0
    
    while true; do
        if [ -z "$max_id" ]; then
            url="${BASE_URL}/api/v1/accounts/${USER_ID}/statuses?limit=40"
        else
            url="${BASE_URL}/api/v1/accounts/${USER_ID}/statuses?limit=40&max_id=${max_id}"
        fi
        
        response=$(curl -s "$url")
        
        if [ "$(echo "$response" | jq '. | length')" -eq 0 ]; then
            break
        fi
        
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
        
        max_id=$(echo "$response" | jq -r '.[-1].id')
        count=$((count + $(echo "$response" | jq '. | length')))
        echo "Fetched $count posts so far..."
    done
    
    echo "Total posts fetched: $count"
}

fetch_all_posts
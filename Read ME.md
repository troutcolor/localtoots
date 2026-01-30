# Mastodon Archive & Search

A simple tool to archive your public Mastodon posts to a local SQLite database and search them via a web interface. Give you a better way to search your toots.

N.B. The web view if there is no search query shows all toot, I've a bit over 1000 and that seems ok, but some pagination might be in order...

## Features

- Fetch all your historical public Mastodon posts
- Regular updates to get new posts
- Search your toots locally (better than Mastodon's native search)
- View boosted posts with original links
- Shows media attachments

## Setup

1. Clone this repository
2. Copy config.example.sh to config.sh: `   cp config.example.sh config.sh`
3. Edit `config.sh` with your Mastodon details:
   - `BASE_URL`: Your Mastodon instance (e.g., `https://mastodon.social`)
   - `USER_ID`: Your user ID (see below)
   - `DB`: Database filename (default: `mastodon_posts.db`)

### Getting Your User ID ###

You need your user id not your user name

Find your user ID with this one-liner (replace my instance and username): `curl -s "https://social.ds106.us/api/v1/accounts/lookup?acct=johnjohnston" | jq -r '.id'`



## Usage

### Initial fetch (get all posts)

` ./getalltoots.sh`

### Regular updates (get new posts only) ###


`./updatetoots.sh`

You can schedule updates with cron:

```
# Run every hour
0 * * * * /path/to/updatetoots.sh
```

### Search Interface

Serve the PHP file with a web server:

`php -S localhost:8000`

Then open `http://localhost:8000/index.php?q=search_term`

## Files

- `getalltoots.sh` - Initial fetch of all historical posts
- `updatetoots.sh` - Fetch only new posts since last update
- `index.php` - Web interface for searching posts
- `config.example.sh` - Example configuration file

## Requirements

- bash
- curl
- jq
- sqlite3
- PHP (for search interface)

## Notes

- This only fetches **public posts** (no authentication required)
- Private/followers-only posts are not included
- If you want to archive private posts, you'll need to add API authentication

## License

CC0 1.0 Universal

# Bavaria Events Crawler - WordPress Plugin

Automatically crawl events from the Bavaria Yachts website and display them in a beautifully formatted table on your WordPress site with English-to-Romanian translation support.

## Features

✨ **Key Features:**
- 🤖 Automatic event crawling from Bavaria Yachts website
- 📅 Events displayed in a table matching the Bavaria Yachts design
- 🔄 Manual refresh button + automatic weekly refresh via WordPress cron
- 🌐 Google Translate API integration for English ↔ Romanian translation
- 📍 Database storage with optimized queries
- 🎯 Shortcode support: `[bavaria_events language="en"]`
- 🎨 Avada theme integration with auto-populate div ID support
- ♿ Responsive design (mobile-friendly)
- 🔒 Security-focused: sanitized inputs, escaped outputs, nonce verification
- 📊 Admin dashboard with crawl logs and status

## Installation

### Step 1: Download & Install Plugin

1. Download the `bavaria-events-plugin` folder
2. Upload to `/wp-content/plugins/` via FTP or WordPress admin
3. Go to WordPress Admin → Plugins
4. Find **Bavaria Events Crawler** and click **Activate**

### Step 2: Configure Google Translate API

#### Get Your API Key:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or use an existing one)
3. Navigate to **APIs & Services** → **Library**
4. Search for **"Cloud Translation API"**
5. Click **Enable**
6. Go to **APIs & Services** → **Credentials**
7. Click **+ CREATE CREDENTIALS** → **API Key**
8. Copy the generated API key (looks like: `AIzaSyD...`)

#### Restrict API Key (Recommended for Security):

1. Click the API key you just created
2. Under **Application restrictions**, select **HTTP referrers**
3. Add: `yourwordpresssite.com/*`
4. Under **API restrictions**, select **Cloud Translation API**
5. Save

#### Add API Key to Plugin:

1. Go to WordPress Admin → **Bavaria Events**
2. Paste your API key in **Google Translate API Key** field
3. Check **Enable Translation** checkbox
4. Select default language display (English or Romanian)
5. Click **Save Settings**

## Usage

### Method 1: Shortcode

Add this to any post or page:

```
[bavaria_events]
```

#### Shortcode Parameters:

- `language="en"` (default) or `language="ro"` - Display language
- `limit="20"` (default) - Maximum events to display
- `sort="date"` (default) or `sort="title"` - Sort order

#### Examples:

```
[bavaria_events language="en" limit="10"]

[bavaria_events language="ro" limit="15" sort="title"]

[bavaria_events]
```

### Method 2: Avada Auto-Populate

For Avada theme users:

1. Go to the page where you want events
2. Add a **Custom HTML** element
3. Enter: `<div id="bavaria-events-table"></div>`
4. Save page

Then in WordPress Admin → **Bavaria Events** → Settings:
1. Enter the Div ID in **Avada Div ID** field: `bavaria-events-table`
2. Save settings

The plugin will automatically populate this div with the events table via JavaScript.

### Manual Refresh

In WordPress Admin → **Bavaria Events**:
- Click **Refresh Events Now** button to manually trigger a crawl
- The plugin will fetch latest events and update the database
- Last crawl status is shown in the Status section

### Automatic Weekly Refresh

The plugin automatically schedules a weekly crawl:
- Runs every week on the same day at the same time
- Updates the database with new/changed events
- Deletes events older than 1 year
- Logs all crawl attempts with success/failure status

## Admin Dashboard

Access at: **WordPress Admin → Bavaria Events**

### Status Section
- Shows total events stored
- Last crawl date & time
- Last crawl result (success/failed)
- Error message if last crawl failed

### Manual Refresh
- **Refresh Events Now** button to manually trigger crawl
- Shows progress and result

### Settings
- **Google Translate API Key** - Your API key for translations
- **Enable Translation** - Toggle English-to-Romanian translation
- **Display Language** - Choose which language to display by default
- **Avada Div ID** - For auto-populating events in custom divs

### Recent Crawl Logs
- Shows last 10 crawl attempts
- Date, status, events found, events updated, duration

## Database Structure

### Tables Created

The plugin creates 3 custom tables:

#### `wp_bavaria_events`
Stores event data:
- `id` - Event ID
- `event_title` - English title
- `event_title_ro` - Romanian title
- `start_date` - Event start date (YYYY-MM-DD)
- `end_date` - Event end date (YYYY-MM-DD)
- `location` - English location
- `location_ro` - Romanian location
- `event_link` - Direct link to event on Bavaria website
- `created_at` - When event was first added
- `updated_at` - Last update time

#### `wp_bavaria_event_logs`
Stores crawl attempt logs:
- `id` - Log ID
- `crawl_date` - When crawl ran
- `status` - 'success', 'partial', or 'failed'
- `events_found` - Number of events found
- `events_updated` - Number of events added/updated
- `error_message` - Error details if failed
- `duration_seconds` - How long crawl took

#### `wp_bavaria_translation_cache`
Caches translations to reduce API calls:
- `id` - Cache ID
- `source_text` - Original English text
- `source_hash` - SHA256 hash of source text
- `translated_text` - Romanian translation
- `source_lang` - Source language ('en')
- `target_lang` - Target language ('ro')
- `created_at` - When translation was cached

## Translation

### How It Works

1. **Automatic Translation**: When enabled, event titles and locations are translated from English to Romanian
2. **Translation Caching**: Translations are cached to reduce Google API calls
3. **Free Tier Limit**: Google's free tier allows 500,000 characters/month (plenty for ~50 events)

### UI Labels Translation

The following UI labels are automatically translated:
- Date → Data
- Location → Locație
- Learn More → Aflați mai multe
- Other Events → Alte Evenimente

### Cost

- **Free tier**: 500,000 characters/month
- **Paid tier**: $15 per 1 million characters (only if you exceed free tier)

For typical use (~50 events updated weekly), you'll stay well within the free tier.

## Troubleshooting

### Events Not Appearing

1. Check **Status** section in admin - see if last crawl succeeded
2. If failed, check error message
3. Click **Refresh Events Now** and wait for it to complete
4. Check browser console for any JavaScript errors

### Translation Not Working

1. Verify Google Translate API key is saved correctly
2. Go to Google Cloud Console and check:
   - Cloud Translation API is **Enabled**
   - API key has **no restrictions** or is restricted to your domain
3. Try **Refresh Events Now** - new events will be translated
4. Check if your Google account has billing set up (even for free tier)

### "No Events Found" Message

1. Click **Refresh Events Now** to manually crawl
2. Wait 1-2 minutes for crawl to complete
3. Check **Recent Crawl Logs** to see if events were found
4. Bavaria Yachts website might have changed structure - check GitHub issues

### Database Errors

The plugin creates tables automatically on activation. If errors occur:
1. Deactivate plugin
2. Reactivate plugin (this will recreate tables)
3. Click **Refresh Events Now**

## Security

### Best Practices Implemented

- ✅ All database inputs sanitized (`sanitize_text_field()`, `esc_url_raw()`)
- ✅ All HTML output escaped (`esc_html()`, `esc_url()`, `esc_attr()`)
- ✅ WordPress nonce verification on admin actions
- ✅ Capability check: only administrators can access settings
- ✅ API credentials stored as WordPress options (not hardcoded)
- ✅ HTTPS validation on remote requests
- ✅ No sensitive data in error logs

### API Key Security

⚠️ **Never share your Google API key!**

- Store it only in WordPress settings (encrypted)
- Restrict it to your website domain
- If compromised, delete key and create new one
- Consider using IP restrictions if you have dedicated server

## Deactivation & Uninstall

### Deactivation
- Plugin stops running cron job
- All data preserved in database
- Can reactivate anytime

### Uninstall
1. Go to Plugins → Bavaria Events Crawler
2. Click **Delete**
3. Choose **Delete all data**
4. **All plugin tables and settings will be permanently deleted**

## System Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **CURL**: Must be enabled for HTTP requests
- **DOM/XML**: Must be enabled for HTML parsing

## Support & Issues

Found a bug or need help?

- Check **Recent Crawl Logs** for error messages
- Review troubleshooting section above
- Report issues on: https://github.com/NylasDev/bavaria-events-plugin-/issues

## Version History

### v1.0.0 (Initial Release)
- ✨ Initial release with full feature set
- Web crawler for Bavaria Yachts events
- Google Translate API integration
- Admin dashboard
- Shortcode support
- Avada theme integration
- Weekly automatic refresh
- Manual refresh button

## License

GPL v2 or later - See LICENSE file

## Credits

Created by Nylas Dev for Bavaria Yachts Events crawling and display.

---

**Happy event crawling!** 🚤⛵

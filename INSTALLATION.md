# Quick Start Guide - Bavaria Events Plugin

## Installation

### 1. Download & Install
```bash
# Download the plugin folder
cd /wp-content/plugins/
unzip bavaria-events-plugin.zip
# OR
git clone https://github.com/NylasDev/bavaria-events-plugin-.git
```

### 2. Activate in WordPress
- Go to **WordPress Admin → Plugins**
- Find **Bavaria Events Crawler**
- Click **Activate**

### 3. Get Google Translate API Key

**Step 1: Google Cloud Setup**
1. Visit [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or use existing)
3. Go to **APIs & Services → Library**
4. Search for **"Cloud Translation API"**
5. Click **Enable**

**Step 2: Create API Key**
1. Go to **APIs & Services → Credentials**
2. Click **+ CREATE CREDENTIALS → API Key**
3. Copy the generated key (format: `AIzaSyD...`)

**Step 3: (Optional) Restrict API Key**
1. Click your API key
2. Under **Application restrictions** → select **HTTP referrers**
3. Add: `yoursite.com/*`
4. Under **API restrictions** → select **Cloud Translation API**
5. Save

### 4. Configure Plugin

1. Go to **WordPress Admin → Bavaria Events**
2. Enter your API key in **Google Translate API Key** field
3. Check **Enable Translation**
4. Select default language (English or Romanian)
5. If using Avada: Enter div ID (e.g., `bavaria-events-table`)
6. Click **Save Settings**

### 5. First Crawl

1. Click **Refresh Events Now** button
2. Wait 2-3 minutes for crawl to complete
3. Check **Recent Crawl Logs** for status
4. If successful, events are now in your database

### 6. Display Events

**Option A: Use Shortcode**
Add this to any post/page:
```
[bavaria_events language="en"]
```

**Option B: Use Avada Div**
1. Add Custom HTML element with: `<div id="bavaria-events-table"></div>`
2. Plugin auto-populates via JavaScript

## Done!

Events will now:
- Display on your website matching Bavaria Yachts design
- Auto-refresh weekly
- Show in English or Romanian
- Automatically translate titles and locations

## Troubleshooting

**Events not showing?**
- Click **Refresh Events Now**
- Check **Recent Crawl Logs** for errors
- Verify Google API key is correct
- Check browser console (F12) for JavaScript errors

**Translation not working?**
- Verify API key is saved
- Check Google Cloud Console - Cloud Translation API is **Enabled**
- Try a manual refresh
- Older events may not be translated until next refresh

**Database errors?**
- Deactivate and reactivate plugin
- Check WordPress error logs
- Ensure MySQL supports InnoDB

## Need Help?

- Check **README.md** for detailed documentation
- Review **Recent Crawl Logs** in admin panel
- Open an issue on GitHub: https://github.com/NylasDev/bavaria-events-plugin-

---

**Version**: 1.0.0  
**License**: GPL v2  
**Author**: Nylas Dev

# Content Performance Heatmap

> See exactly where readers engage with your content — so you can make every post better.

## What does this plugin do?

This plugin shows you a color-coded map of your blog posts that reveals where readers actually spend time, which paragraphs they read carefully, and where they lose interest and leave. Instead of guessing what works, you'll see visual proof of which sections grab attention and which ones get skipped, so you can improve your content in the places that matter most.

## Why you need it

- **Stop guessing, start knowing** — See exactly which paragraphs keep readers engaged and which ones cause them to leave your site
- **Make smarter content decisions** — Know whether to expand popular sections, rewrite boring parts, or move important information higher up
- **Increase reader engagement** — Use real data to restructure your posts so more people read all the way to your call-to-action or affiliate links

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- JavaScript must be enabled in visitors' browsers (it is by default)
- Works with any WordPress theme

## Installation

1. Download the `content-performance-heatmap.zip` file to your computer
2. In your WordPress admin area, go to **Plugins** → **Add New** → **Upload Plugin**
3. Click the **Choose File** button and select the zip file you downloaded
4. Click **Install Now**, then click **Activate Plugin**
5. You're done! The plugin will start collecting data from your visitors immediately

## How to use it

1. After activation, go to **Posts** → **All Posts** in your WordPress admin menu
2. Find any published post and click **View Heatmap** (this link appears below each post title)
3. You'll see your actual post content with colored overlays: **red** means high engagement, **yellow** means moderate engagement, and **blue** means low engagement or skipped sections
4. Look at the sidebar to see detailed statistics: scroll depth percentage, average time spent per section, and click counts
5. Click the **Export Report** button at the top to download a PDF summary you can share with your team
6. To adjust settings, go to **Settings** → **Performance Heatmap** where you can choose which post types to track and set minimum visitor thresholds before showing data

## Features

- Visual color-coded heatmaps overlaid directly on your post content
- Tracks scroll depth to see how far down the page readers actually go
- Measures time spent on each paragraph and section
- Records clicks on links, buttons, and images within your content
- Works automatically on all posts once activated
- Minimum visitor threshold prevents misleading data from just a few visitors
- Mobile and desktop tracking shown separately
- Export heatmap reports as PDF files
- Respects Do Not Track browser settings and privacy regulations
- Lightweight tracking code that doesn't slow down your site
- Compatible with caching plugins

## Frequently Asked Questions

**Q: Will this slow down my site?**

A: No. The tracking code is tiny (under 5KB) and loads asynchronously, which means it doesn't block your page from displaying. Your readers won't notice any difference in loading speed.

**Q: Is this compatible with page builders like Elementor?**

A: Yes. The heatmap works with any page builder including Elementor, Divi, Beaver Builder, and Gutenberg. As long as your content is inside the main post area, it will be tracked and displayed on the heatmap.

**Q: What happens if I deactivate the plugin?**

A: All your collected heatmap data is safely stored in your WordPress database. If you deactivate the plugin, tracking stops immediately but your historical data remains. If you reactivate later, you'll still have access to all your past heatmaps. If you delete the plugin entirely, all data is removed.

**Q: How many visitors do I need before the heatmap shows useful data?**

A: By default, the plugin waits until at least 50 visitors have viewed a post before displaying the heatmap. This ensures the data is meaningful and not skewed by just one or two people. You can adjust this number in **Settings** → **Performance Heatmap** under the "Minimum Visitors" option.

**Q: Does this track personal information about my visitors?**

A: No. The plugin only tracks anonymous behavior like scroll position and time spent. It doesn't collect names, email addresses, IP addresses, or any personally identifiable information. It's designed to respect privacy while giving you useful content insights.

## Changelog

### 1.0.0 — 5 May 2026
- Initial release
- Visual heatmap overlay on post content with color-coded engagement levels
- Scroll depth, time spent, and click tracking functionality
- Separate mobile and desktop analytics
- PDF export feature for heatmap reports
- Settings page with customizable tracking options

## License

GPL v2 or later. https://www.gnu.org/licenses/gpl-2.0.html

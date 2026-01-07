=== Crypto Miner Tycoon ===
Contributors: jackofall1232
Donate link: https://ShortcodeArcade.com
Tags: game, idle game, crypto, clicker game, mining game
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 0.3.4
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An engaging crypto-themed idle clicker game with Elo-balanced progression, cloud saves, and competitive leaderboards.

== Description ==

Crypto Miner Tycoon is an addictive idle clicker game where players build their cryptocurrency mining empire from scratch. Click to mine satoshis, purchase upgrades, and watch your passive income grow exponentially!

**Core Features:**

* **Click-to-Mine Mechanics** – Earn satoshis by clicking the glowing Bitcoin symbol
* **Elo-Balanced Progression** – Sophisticated rating system ensures balanced gameplay
* **10 Unique Upgrades** – From Basic Pickaxes to Black Hole Extractors
* **Prestige System** – "Hard Fork" to reset with permanent +10% production bonuses (stackable!)
* **Auto-Save** – Game progress saves automatically every 10 seconds
* **Offline Progress** – Earn passive income while away (up to 24 hours)
* **Beautiful Design** – Cyberpunk-inspired glassmorphic UI with neon effects
* **Mobile Responsive** – Play seamlessly on any device

**Advanced Features (Optional):**

* **Cloud Saves** – Save player progress to WordPress database (requires user login)
* **Leaderboards** – Display top miners with prestige-weighted scoring
* **Ad Integration** – Built-in ad space for monetization
* **REST API** – Full API for cloud saves and leaderboard data

**How the Elo System Works:**

The game uses an Elo-inspired rating system (similar to chess ratings) to balance upgrade costs and progression. As players advance, upgrade costs scale dynamically to maintain a fair difficulty curve while rewarding strategic decisions.

Recent updates further refine this system to prevent runaway scaling and repetitive upgrade stacking.

**Shortcodes:**

Display the game:  
`[crypto_miner_tycoon]`

Display the game with custom ad code:  
`[crypto_miner_tycoon ad_code="<your ad network code>"]`

Display the leaderboard (requires cloud saves enabled):  
`[crypto_miner_leaderboard]`

**Perfect For:**

* Cryptocurrency blogs and news sites
* Gaming communities and portals
* Educational sites teaching about blockchain
* Crypto-related communities
* Anyone wanting engaging, interactive content

**Cloud Saves & Leaderboards:**

Enable cloud saves in **Settings > Crypto Miner Tycoon** to:
- Store player progress in your WordPress database
- Require user login for save functionality
- Enable competitive leaderboards
- Track top players with medals and rankings
- Keep all data on your own server

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins > Add New
3. Search for "Crypto Miner Tycoon"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Upload the `crypto-miner-tycoon` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress

= After Installation =

1. Add the shortcode `[crypto_miner_tycoon]` to any page or post
2. (Optional) Go to Settings > Crypto Miner Tycoon to enable cloud saves and leaderboards
3. (Optional) Add your ad code via shortcode attribute

== Frequently Asked Questions ==

= How do I display the game on my site? =

Add the shortcode `[crypto_miner_tycoon]` to any page or post.

= Does the game save progress? =

Yes. The game auto-saves locally every 10 seconds. Optional cloud saves can be enabled by the site administrator.

= What's the difference between local saves and cloud saves? =

**Local Saves:** Stored in the browser. No login required.  
**Cloud Saves:** Stored in the WordPress database. Login required. Enables leaderboards.

= How do I enable the leaderboard? =

1. Go to Settings > Crypto Miner Tycoon
2. Enable Cloud Saves
3. Enable Leaderboard
4. Add `[crypto_miner_leaderboard]` to a page

= Can I add advertisements? =

Yes. Pass ad code via shortcode:  
`[crypto_miner_tycoon ad_code="<your ad code>"]`

= Is the game mobile-friendly? =

Yes. The interface is fully responsive.

= How does the prestige system work? =

At 1,000,000 satoshis, players can perform a "Hard Fork", resetting progress for a permanent +10% production bonus per prestige level.

= Where is player data stored? =

Local saves are stored in the browser. Cloud saves are stored in WordPress custom database tables when enabled.

== Screenshots ==

1. Main game interface with cyberpunk design and glowing Bitcoin mining button  
2. Upgrades panel with Elo-balanced progression  
3. Prestige system with permanent bonuses  
4. Mobile responsive layout  
5. Leaderboard with medals and rankings  
6. Admin settings panel  

== Changelog ==

= 0.3.4 - 2025-01-06 =
* Improved: Elo progression algorithm for fairer long-term gameplay
* Added: Exponential prestige cost scaling (5x multiplier per prestige)
* Added: Diminishing returns on duplicate upgrades (80%, 60%, 40%, 20%)
* Improved: Overall progression pacing and balance
* No breaking changes or data migrations

= 0.3.3 - 2025-01-04 =
* Fixed: All WordPress Plugin Checker and PHPCS warnings
* Improved: Database query handling for custom tables
* Improved: Script and style enqueue versioning for cache reliability
* Improved: Admin dashboard status checks for cloud saves
* Improved: Code comments and inline documentation
* No gameplay or data changes

= 0.3.0 - 2025-01-01 =
* Added: Cloud save system with WordPress integration
* Added: Competitive leaderboard with prestige-weighted scoring
* Added: Admin settings panel
* Added: REST API for cloud saves and leaderboard
* Added: Offline progress (up to 24 hours)
* Added: Leaderboard shortcode
* Fixed: Prestige multiplier calculation
* Improved: Code organization and standards compliance

= 0.2.0 - 2024-12-XX =
* Added: Prestige / Hard Fork system
* Added: Auto-save functionality
* Improved: UI animations and responsiveness

= 0.1.0 - 2024-12-XX =
* Initial beta release
* Click-to-mine mechanics
* Elo-balanced progression
* Cyberpunk UI design

== Upgrade Notice ==

= 0.3.4 =
Gameplay balance update improving progression fairness and prestige scaling. No breaking changes.

= 0.3.3 =
Maintenance release. Improves code quality, plugin checker compliance, and admin stability.

= 0.3.0 =
Major update introducing cloud saves, leaderboards, and offline progress. Cloud saves are optional and disabled by default.

== Credits ==

Developed by: jackofall1232  
Website: https://ShortcodeArcade.com

Special Thanks:
* Google Fonts: Orbitron and Rajdhani
* WordPress community contributors

== Privacy Policy ==

Crypto Miner Tycoon respects user privacy:

* Local saves are stored in browser localStorage
* Cloud saves (optional) are stored in WordPress custom tables
* No external APIs or third-party tracking
* No analytics or telemetry
* Administrators control all stored data

Site owners are responsible for updating their privacy policy if cloud saves are enabled.

=== FilterFlow Posts ===
Contributors: ilfarhanahmed
Tags: elementor, posts, filter, ajax, grid
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A responsive, accessible, AJAX-powered filterable posts grid widget for Elementor.

== Description ==

FilterFlow Posts adds a modern filterable posts grid to the Elementor editor without requiring Elementor Pro.

= Responsive filtering =

* Desktop category chips automatically move excess categories into a More menu.
* Filter icons are enabled by default: All uses a blue active grid icon and categories receive automatic icons based on their names, with per-category overrides available.
* Tablet uses one category dropdown by default, avoiding duplicate controls.
* Mobile keeps Filter and All visible in a fixed 60:40 row.
* The active mobile category is moved to the first quick-filter position.
* Responsive switching uses the Elementor widget width, not only the browser width.
* A header-collision guard prevents common theme or Elementor headers from covering the widget, with an optional manual-clearance fallback for unusual custom headers.

= Post cards =

* Featured image, title, smart excerpt, multiple category badges, author meta, avatar, date, reading time, comments, and arrow link.
* Smart excerpts prioritize the manual WordPress excerpt, then Elementor Text Editor content while ignoring Elementor Heading widgets.
* Numbered pagination, Load More, or no pagination.
* Responsive one-to-four-column layouts.

= Design controls =

* Pill, outline, underline, overline, double-line, tab, and minimal filter styles.
* Full filter-bar background, gradient, shadow, border, radius, padding, width, alignment, and sticky controls.
* Complete category-badge colors, typography, spacing, border, radius, shadow, hover, and featured-image overlay positions.
* Post-title typography, weight, normal and hover colors, and optional text decoration.
* Card, image, excerpt, metadata, pagination, heading, and description customization.

= Accessibility and privacy =

* Keyboard-accessible controls, focus trapping, ARIA state updates, live result announcements, and reduced-motion support.
* No tracking, advertising, custom database tables, cookies, or plugin-wide stored options.
* Widget settings are stored only as part of the Elementor page data.

== Installation ==

1. Install and activate Elementor 3.20.0 or newer.
2. Upload the FilterFlow Posts ZIP through Plugins > Add New > Upload Plugin.
3. Activate FilterFlow Posts.
4. Edit a page with Elementor.
5. Find FilterFlow Posts in the FilterFlow widget category and drag it onto the page.
6. Select categories and customize the content, layout, filters, cards, and pagination.

== Screenshots ==

1. Desktop post grid with icon-based category filters and image-overlay badges.
2. Mobile responsive filtering with the active category positioned first.
3. Tablet category selector and responsive post grid.
4. Elementor editor controls and live FilterFlow Posts preview.

== Frequently Asked Questions ==

= Does FilterFlow Posts require Elementor Pro? =

No. It works with the free Elementor plugin.

= Why does tablet show only one category control? =

The default tablet layout intentionally uses one dropdown so Filter and selected-category controls do not duplicate the same action. Auto-fit Chips + More remains available in the widget settings.

= How does the mobile filter layout work? =

Filter and All remain visible in a 60:40 row. Quick categories appear below, and the active category is automatically placed first. Every category remains available through Filter.

= Can the filter bar avoid a sticky or absolute-positioned header? =

Yes. Prevent Header Overlap is enabled by default and measures common WordPress and Elementor header structures. Header Safety Gap and Manual Header Clearance controls are also available.

= Why is my Elementor Heading widget not repeated in the excerpt? =

Smart Elementor Content ignores Heading widgets and extracts prose from Text Editor widgets. A manually entered WordPress excerpt always takes priority.

= Does the plugin collect personal data? =

No. It does not send data to external services, create tracking cookies, or maintain its own user-data store.

== Privacy ==

FilterFlow Posts does not collect, sell, or share personal data. AJAX requests are sent only to the site where the plugin is installed and contain the selected category, page number, and strictly limited widget query settings needed to render public posts.

When the optional author avatar is enabled, the plugin uses WordPress core get_avatar(). Depending on the site avatar settings, the generated image URL may use an external avatar provider such as Gravatar. Disable Author Avatar in the widget when external avatar requests are not desired.

== Changelog ==

= 1.0.0 — Build 20260804.1430 =
* Moved category badge placement controls into a dedicated Content tab section.
* Added above-image and nine-point image-overlay placement options.
* Fixed left, center, and right alignment for non-overlay category badges.
* Added overlay edge-offset and above-image padding controls.

= 1.0.0 =
* Internal build 20260804.1425 gives the More button a direct event path and preserves an open overflow menu during responsive layout measurements.
* Internal build 20260804.1410 adds reliable compact-width chip and More-menu interactions plus asset cache busting.

* Initial public release.
* Added collision-safe desktop, tablet, and mobile filter layouts with automatic and manual header clearance.
* Added a single-control tablet dropdown to remove duplicate filter actions.
* Added a fixed mobile Filter and All row with a 60:40 ratio.
* Added active-category-first mobile quick filters.
* Added built-in automatic SVG icons for All and common category names, plus Elementor icon overrides.
* Added category-badge positions over featured images.
* Added smart Elementor excerpts, multiple category badges, author meta, responsive cards, AJAX pagination, accessibility support, and extensive Elementor styling controls.

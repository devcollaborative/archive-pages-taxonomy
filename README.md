Archive Pages - Taxonomy
===

Replace WordPress's default auto-generated term archive with a manually created Page. For each term, select the page from a dropdown of all site pages.  Good for creating topic landing pages. 

## Installation

Clone or download this repository and add it to the `wp-content/plugins` directory.

## How to Use
Go to **Settings > Archive Pages** and choose a page to set as the archive page for each taxonomy term. Pages must already be published. 

When a selected page is saved, the term_link filter replaces that page's URL replaces the taxonomy link ( term_link ) so that the user is directed to the taxonomy landing page instead of the auto-generated archive. 

The term archive will still exist at its assigned URL, but there will be no navigation to it. 

Available taxonomies are currently hardcoded: category, post_tag. Add more taxonomies to array $our_taxonomies in function archive_pages_settings_init()

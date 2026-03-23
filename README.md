# KSAS Faculty Books

A specialized WordPress plugin that registers and manages a **Faculty Books** Custom Post Type (CPT).

---

## 🚀 Features

* **Custom Post Type:** Dedicated "Faculty Books" menu in the WordPress dashboard with a `dashicons-book-alt` icon.
* **Relational Metadata:** Integrates with the `people` CPT to link books directly to faculty profiles.
* **Advanced Custom Fields (ACF) Integration:** * PHP-registered field groups for version control.
    * Conditional logic to show/hide secondary author fields.
* **High-Performance Widget:**
    * Supports "Latest" or "Random" display modes.
    * Taxonomy filtering by "Program."
    * Optimized database queries using `no_found_rows` and `fields => ids`.
* **Security & Standards:** * Fully **PHPCS** compliant.
    * Strict data sanitization (`sanitize_text_field`, `esc_url_raw`).
    * Late escaping on all frontend outputs.

---

## 🛠 Technical Specifications

### Data Compatibility
The plugin uses `wp_validate_boolean()` to handle the transition from legacy checkbox data (stored as `"on"`) to modern ACF boolean data (stored as `1`/`0`). This ensures no data is lost during the upgrade to ACF.

### Performance Optimization
To prevent slow queries in the WordPress admin and on the frontend, all `get_posts` calls utilize performance flags:
* `no_found_rows => true`: Skips the expensive pagination count.
* `update_post_term_cache => false`: Reduces database hits when terms aren't required.
* `fields => ids`: Minimizes memory usage by fetching only necessary post IDs.

---

## 📂 Installation

1.  Upload the `ksas-faculty-books` folder to your `/wp-content/plugins/` directory.
2.  Activate the plugin in the WordPress Admin.
3.  **Required:** Ensure the **Advanced Custom Fields (ACF)** plugin is active to render the book metadata fields.

---

## 🎨 Theme Integration (Tailwind CSS)

This plugin is optimized for the **KSAS Department Tailwind** theme. The suggested template partial handles:
* Responsive book cover images.
* Conditional rendering of Publisher and Date strings (smart comma logic).
* Automatic capitalization of roles (Author, Editor, etc.) using Tailwind's `capitalize` utility.

---

## 📄 License

**License:** GPL2  
**Author:** KSAS Communications  
**Organization:** Johns Hopkins University - Krieger School of Arts & Sciences
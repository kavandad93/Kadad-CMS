# Kadad CMS

**Version:** 0.0.1  
**Status:** Early foundation / development preview  
**Purpose:** A flexible, all-purpose website CMS with a strong focus on a simple, modern commerce experience.

## Product direction

Kadad CMS aims to make it straightforward to build and manage a complete website: online stores, pages, articles, media, tools and future modules. The store is a first-class capability, not the only capability.

## v0.0.1 foundation

- A Kadad CMS admin landing page with a compact overview.
- A dedicated Products content type.
- Product categories.
- Product price, SKU and stock fields in the admin editor.
- Persian-friendly, right-to-left admin presentation.
- Implemented as a WordPress must-use plugin so the WordPress core remains untouched.

## Install / development

This repository currently contains WordPress core. The Kadad-specific foundation is in:

`wp-content/mu-plugins/kadad-cms.php`

Must-use plugins load automatically from `wp-content/mu-plugins`; no activation step is required. Back up the database and files before testing on an existing installation.

## Current limitations

This is an early development preview, not a production-ready commerce platform. Version 0.0.1 does **not** yet implement cart, checkout, payment gateways, customer accounts, tax/shipping calculations, order processing, or a storefront theme. Those need dedicated design, validation and security work before real transactions.

## Roadmap

1. v0.0.1 — CMS foundation and product management fields.
2. v0.0.2 — Product editor UX, media/gallery and product listing improvements.
3. v0.1.0 — Storefront templates and catalog/search.
4. Later — Cart, checkout, orders, customer accounts, payments, extensions and visual site building.

## License

WordPress is licensed under GPLv2 or later. Kadad-specific code in this repository is intended to remain compatible with the repository's existing license; review the repository's `license.txt` before redistribution.

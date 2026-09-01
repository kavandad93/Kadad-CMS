# Kadad CMS Subscription Service

Kadad CMS is delivered as a WordPress MU plugin, so WordPress core, normal plugins, themes, media and user management remain unchanged.

## Installation

1. Configure the standard WordPress `wp-config.php` database settings.
2. Browse to `/license.php`, enter the subscription code, and complete the normal WordPress installer.
3. Log in as an administrator. The bootstrap activation is migrated to the server-side `kadad_cms_license` option.
4. Manage renewal and validation settings in **Kadad CMS → My Subscription** and **Kadad CMS → Settings**.

The pre-install record is stored as a non-outputting PHP file at `wp-content/.kadad-license.php` with restrictive permissions. It is intentionally never exposed in HTML, JavaScript, the REST API, dashboard, or logs. Once WordPress is installed, the code remains a server-side option and is only shown masked to administrators.

## License design

`CMS_License_Client` uses the WordPress HTTP API and calls `https://wnat.ir/api/{URL-encoded-code}/` for validation and `?time=1` for remaining time. It validates JSON and the boolean `ok` field before using a response. Periodic checks use the configurable validation interval. Network, non-2xx, and malformed-response failures preserve a previously validated subscription within the configurable grace period (default: 72 hours); afterward the status is `validation_required`. The public site remains online.

To use a different server, update **Kadad CMS → Settings → License API server**. The value must be the API base path, for example `https://license.example.test/api`.

## Testing

Use a non-production license code to test activation, invalid/empty code handling, and a test HTTP proxy or unavailable host for failure/grace handling. Check anonymous REST access returns 401/403 and an administrator can call `GET /wp-json/cms/v1/subscription` with a WordPress REST nonce. Standard WordPress plugin/theme/media/user workflows are deliberately preserved because the implementation only adds an MU plugin and does not replace core APIs.

## Removal

MU plugins are not removable from the normal plugins screen. Remove the `wp-content/mu-plugins/cms-license.php` loader and `cms-core` directory only after deliberately deleting `kadad_cms_license` and `kadad_cms_license_logs` options (and `wp-content/.kadad-license.php` if no longer needed).

<?php
/** Kadad CMS pre-install subscription gate. */
declare( strict_types=1 );
$storage = __DIR__ . '/wp-content/.kadad-license.php';
$error = '';
function kadad_license_request( string $code ): array {
	$url = 'https://wnat.ir/api/' . rawurlencode( $code ) . '/';
	$context = stream_context_create( array( 'http' => array( 'method' => 'GET', 'timeout' => 12, 'header' => "Accept: application/json\r\n" ), 'ssl' => array( 'verify_peer' => true, 'verify_peer_name' => true ) ) );
	$body = @file_get_contents( $url, false, $context ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$data = is_string( $body ) ? json_decode( $body, true ) : null;
	return is_array( $data ) && isset( $data['ok'] ) && is_bool( $data['ok'] ) ? $data : array( 'ok' => false, 'error' => 'unavailable' );
}
if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	$token = $_POST['kadad_license_nonce'] ?? ''; $expected = $_COOKIE['kadad_license_nonce'] ?? '';
	if ( ! is_string( $token ) || ! is_string( $expected ) || ! hash_equals( $expected, $token ) ) { $error = 'درخواست نامعتبر است. لطفاً صفحه را دوباره باز کنید.'; }
	else { $code = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) ( $_POST['subscription_code'] ?? '' ) ); if ( '' === $code ) $error = 'کد اشتراک را وارد کنید.'; else { $response = kadad_license_request( $code ); if ( empty( $response['ok'] ) ) $error = 'کد معتبر نیست یا سرور لایسنس در دسترس نیست.'; else { $payload = array( 'code' => $code, 'activated_at' => time(), 'response' => $response ); $content = "<?php http_response_code(403); exit; ?>\n" . json_encode( $payload, JSON_UNESCAPED_SLASHES ); if ( false === file_put_contents( $storage, $content, LOCK_EX ) ) $error = 'ذخیره‌سازی لایسنس ممکن نیست. مجوز wp-content را بررسی کنید.'; else { @chmod( $storage, 0600 ); header( 'Location: wp-admin/install.php', true, 302 ); exit; } } }
	}
}
$nonce = bin2hex( random_bytes( 24 ) ); setcookie( 'kadad_license_nonce', $nonce, array( 'expires' => time() + 1800, 'path' => '/', 'secure' => isset( $_SERVER['HTTPS'] ), 'httponly' => true, 'samesite' => 'Strict' ) );
?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>فعال‌سازی کاداد CMS</title><style>body{margin:0;background:#f1f5f9;color:#0f172a;font-family:Tahoma,Arial,sans-serif;display:grid;min-height:100vh;place-items:center}.card{width:min(440px,calc(100% - 32px));background:#fff;border-radius:20px;padding:36px;box-sizing:border-box;box-shadow:0 18px 50px #0f172a18}.mark{width:48px;height:48px;border-radius:14px;background:#2563eb;color:#fff;display:grid;place-items:center;font-size:24px;font-weight:bold}h1{margin:20px 0 10px;font-size:26px}p{line-height:1.8;color:#475569}label,input,button{display:block;width:100%;box-sizing:border-box}input{margin:8px 0 16px;padding:13px;border:1px solid #cbd5e1;border-radius:9px;font-size:16px;direction:ltr}button{border:0;border-radius:9px;background:#2563eb;color:#fff;padding:13px;font-size:16px;cursor:pointer}.error{background:#fef2f2;color:#b91c1c;padding:12px;border-radius:9px;margin:14px 0}.hint{font-size:12px}</style></head><body><main class="card"><div class="mark">K</div><h1>فعال‌سازی Kadad CMS</h1><p>برای شروع نصب WordPress، کد اشتراک معتبر خود را وارد کنید.</p><?php if ( $error ) : ?><div class="error"><?php echo htmlspecialchars( $error, ENT_QUOTES, 'UTF-8' ); ?></div><?php endif; ?><form method="post"><input type="hidden" name="kadad_license_nonce" value="<?php echo htmlspecialchars( $nonce, ENT_QUOTES, 'UTF-8' ); ?>"><label for="subscription_code">Subscription Code</label><input id="subscription_code" name="subscription_code" required autocomplete="off" autofocus><button type="submit">فعال‌سازی و ادامه</button></form><p class="hint">اعتبارسنجی به‌صورت امن در سرور انجام می‌شود.</p></main></body></html>

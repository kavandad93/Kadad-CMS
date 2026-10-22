<?php
/**
 * Plugin Name: Kadad CMS Foundation
 * Description: The first Kadad CMS foundation: a simple admin home and product management fields.
 * Version: 0.0.1
 * Author: Kadad
 * Text Domain: kadad-cms
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KADAD_CMS_VERSION', '0.0.1' );

/**
 * Register the first commerce content model.
 */
function kadad_cms_register_product_content_type() {
	$labels = array(
		'name'                  => 'محصولات',
		'singular_name'         => 'محصول',
		'menu_name'             => 'محصولات',
		'name_admin_bar'        => 'محصول',
		'add_new'               => 'افزودن محصول',
		'add_new_item'          => 'افزودن محصول جدید',
		'new_item'              => 'محصول جدید',
		'edit_item'             => 'ویرایش محصول',
		'view_item'             => 'نمایش محصول',
		'all_items'             => 'همه محصولات',
		'search_items'          => 'جست‌وجوی محصولات',
		'not_found'             => 'محصولی پیدا نشد.',
		'not_found_in_trash'    => 'محصولی در زباله‌دان نیست.',
		'featured_image'        => 'تصویر محصول',
		'set_featured_image'    => 'انتخاب تصویر محصول',
		'remove_featured_image' => 'حذف تصویر محصول',
		'use_featured_image'    => 'استفاده به‌عنوان تصویر محصول',
	);

	register_post_type(
		'kadad_product',
		array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'kadad-cms',
			'show_in_rest'       => true,
			'has_archive'        => false,
			'rewrite'            => false,
			'menu_position'      => 6,
			'menu_icon'          => 'dashicons-store',
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		)
	);

	register_taxonomy(
		'kadad_product_category',
		array( 'kadad_product' ),
		array(
			'labels' => array(
				'name'              => 'دسته‌بندی محصولات',
				'singular_name'     => 'دسته‌بندی محصول',
				'search_items'      => 'جست‌وجوی دسته‌بندی‌ها',
				'all_items'         => 'همه دسته‌بندی‌ها',
				'parent_item'       => 'دسته‌بندی مادر',
				'parent_item_colon' => 'دسته‌بندی مادر:',
				'edit_item'         => 'ویرایش دسته‌بندی',
				'update_item'       => 'به‌روزرسانی دسته‌بندی',
				'add_new_item'      => 'افزودن دسته‌بندی',
				'new_item_name'     => 'نام دسته‌بندی جدید',
				'menu_name'         => 'دسته‌بندی‌ها',
			),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'kadad_cms_register_product_content_type' );

/**
 * Add product data fields to the editor.
 */
function kadad_cms_add_product_meta_box() {
	add_meta_box(
		'kadad-cms-product-data',
		'اطلاعات محصول',
		'kadad_cms_render_product_meta_box',
		'kadad_product',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_kadad_product', 'kadad_cms_add_product_meta_box' );

function kadad_cms_render_product_meta_box( $post ) {
	wp_nonce_field( 'kadad_cms_save_product_data', 'kadad_cms_product_nonce' );

	$sku   = get_post_meta( $post->ID, '_kadad_cms_sku', true );
	$price = get_post_meta( $post->ID, '_kadad_cms_price', true );
	$stock = get_post_meta( $post->ID, '_kadad_cms_stock', true );
	?>
	<div class="kadad-cms-fields" dir="rtl">
		<p>
			<label for="kadad-cms-sku"><strong>شناسه محصول (SKU)</strong></label>
			<input id="kadad-cms-sku" name="kadad_cms_sku" type="text"
				value="<?php echo esc_attr( $sku ); ?>" class="widefat"
				placeholder="مثلاً KD-1001" />
		</p>
		<p>
			<label for="kadad-cms-price"><strong>قیمت</strong></label>
			<input id="kadad-cms-price" name="kadad_cms_price" type="number"
				min="0" step="0.01" value="<?php echo esc_attr( $price ); ?>"
				class="widefat" inputmode="decimal" placeholder="0" />
			<span class="description">واحد پول در این نسخه هنوز قابل تنظیم نیست؛ قبل از استفاده فروشگاهی باید تعیین شود.</span>
		</p>
		<p>
			<label for="kadad-cms-stock"><strong>موجودی</strong></label>
			<input id="kadad-cms-stock" name="kadad_cms_stock" type="number"
				min="0" step="1" value="<?php echo esc_attr( $stock ); ?>"
				class="widefat" placeholder="0" />
		</p>
	</div>
	<?php
}

/**
 * Validate and save product data.
 */
function kadad_cms_save_product_meta( $post_id ) {
	if ( ! isset( $_POST['kadad_cms_product_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['kadad_cms_product_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'kadad_cms_save_product_data' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$sku = isset( $_POST['kadad_cms_sku'] )
		? sanitize_text_field( wp_unslash( $_POST['kadad_cms_sku'] ) )
		: '';

	$price_raw = isset( $_POST['kadad_cms_price'] )
		? trim( (string) wp_unslash( $_POST['kadad_cms_price'] ) )
		: '';

	$stock_raw = isset( $_POST['kadad_cms_stock'] )
		? trim( (string) wp_unslash( $_POST['kadad_cms_stock'] ) )
		: '';

	update_post_meta( $post_id, '_kadad_cms_sku', $sku );

	if ( '' === $price_raw ) {
		delete_post_meta( $post_id, '_kadad_cms_price' );
	} elseif ( is_numeric( $price_raw ) && (float) $price_raw >= 0 ) {
		update_post_meta( $post_id, '_kadad_cms_price', (string) (float) $price_raw );
	}

	if ( '' === $stock_raw ) {
		delete_post_meta( $post_id, '_kadad_cms_stock' );
	} elseif ( ctype_digit( $stock_raw ) ) {
		update_post_meta( $post_id, '_kadad_cms_stock', (string) absint( $stock_raw ) );
	}
}
add_action( 'save_post_kadad_product', 'kadad_cms_save_product_meta' );

/**
 * Add useful columns to the product list.
 */
function kadad_cms_product_admin_columns( $columns ) {
	$custom = array();

	foreach ( $columns as $key => $label ) {
		$custom[ $key ] = $label;

		if ( 'title' === $key ) {
			$custom['kadad_sku']   = 'شناسه';
			$custom['kadad_price'] = 'قیمت';
			$custom['kadad_stock'] = 'موجودی';
		}
	}

	return $custom;
}
add_filter( 'manage_kadad_product_posts_columns', 'kadad_cms_product_admin_columns' );

function kadad_cms_render_product_admin_column( $column, $post_id ) {
	$meta_key = '';

	if ( 'kadad_sku' === $column ) {
		$meta_key = '_kadad_cms_sku';
	} elseif ( 'kadad_price' === $column ) {
		$meta_key = '_kadad_cms_price';
	} elseif ( 'kadad_stock' === $column ) {
		$meta_key = '_kadad_cms_stock';
	}

	if ( $meta_key ) {
		$value = get_post_meta( $post_id, $meta_key, true );
		echo '' !== (string) $value ? esc_html( $value ) : '<span aria-label="ثبت نشده">—</span>';
	}
}
add_action( 'manage_kadad_product_posts_custom_column', 'kadad_cms_render_product_admin_column', 10, 2 );

/**
 * Add a Kadad CMS landing page to the WordPress admin.
 */
function kadad_cms_register_admin_menu() {
	add_menu_page(
		'Kadad CMS',
		'Kadad CMS',
		'edit_posts',
		'kadad-cms',
		'kadad_cms_render_admin_page',
		'dashicons-store',
		3
	);
}
add_action( 'admin_menu', 'kadad_cms_register_admin_menu' );

function kadad_cms_render_admin_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'شما اجازه دسترسی به این صفحه را ندارید.', 'kadad-cms' ) );
	}

	$product_counts = wp_count_posts( 'kadad_product' );
	$published      = isset( $product_counts->publish ) ? (int) $product_counts->publish : 0;
	$drafts          = isset( $product_counts->draft ) ? (int) $product_counts->draft : 0;
	$pages           = wp_count_posts( 'page' );
	$published_pages = isset( $pages->publish ) ? (int) $pages->publish : 0;
	?>
	<div class="wrap kadad-cms-admin" dir="rtl">
		<header class="kadad-cms-hero">
			<div class="kadad-cms-brand">
				<span class="kadad-cms-mark" aria-hidden="true">K</span>
				<div>
					<p class="kadad-cms-eyebrow">مدیریت یکپارچه سایت</p>
					<h1>Kadad CMS <span class="kadad-cms-version">v<?php echo esc_html( KADAD_CMS_VERSION ); ?></span></h1>
					<p>سایتت را بساز، محصولاتت را مدیریت کن و قدم‌به‌قدم امکانات تازه اضافه کن.</p>
				</div>
			</div>
			<a class="kadad-cms-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=kadad_product' ) ); ?>">＋ افزودن محصول</a>
		</header>

		<section class="kadad-cms-stats" aria-label="خلاصه سایت">
			<div class="kadad-cms-stat">
				<span class="kadad-cms-stat-icon">▦</span>
				<div><span>محصولات منتشرشده</span><strong><?php echo esc_html( number_format_i18n( $published ) ); ?></strong></div>
			</div>
			<div class="kadad-cms-stat">
				<span class="kadad-cms-stat-icon">◷</span>
				<div><span>پیش‌نویس محصولات</span><strong><?php echo esc_html( number_format_i18n( $drafts ) ); ?></strong></div>
			</div>
			<div class="kadad-cms-stat">
				<span class="kadad-cms-stat-icon">▤</span>
				<div><span>برگه‌های منتشرشده</span><strong><?php echo esc_html( number_format_i18n( $published_pages ) ); ?></strong></div>
			</div>
		</section>

		<div class="kadad-cms-section-heading">
			<div><h2>شروع سریع</h2><p>ابزارهای اصلی برای مدیریت سایت</p></div>
		</div>

		<section class="kadad-cms-cards">
			<a class="kadad-cms-card" href="<?php echo esc_url( admin_url( 'edit.php?post_type=kadad_product' ) ); ?>">
				<span class="kadad-cms-card-icon">🛍️</span>
				<h3>محصولات</h3><p>محصولات، توضیحات، تصویر، قیمت و موجودی را مدیریت کن.</p>
				<span class="kadad-cms-card-link">مدیریت محصولات <span aria-hidden="true">←</span></span>
			</a>
			<a class="kadad-cms-card" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=kadad_product_category&post_type=kadad_product' ) ); ?>">
				<span class="kadad-cms-card-icon">▦</span>
				<h3>دسته‌بندی‌ها</h3><p>محصولات را برای پیدا کردن و مدیریت آسان‌تر گروه‌بندی کن.</p>
				<span class="kadad-cms-card-link">مدیریت دسته‌بندی‌ها <span aria-hidden="true">←</span></span>
			</a>
			<a class="kadad-cms-card" href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">
				<span class="kadad-cms-card-icon">▤</span>
				<h3>برگه‌های سایت</h3><p>صفحه‌هایی مثل درباره ما، تماس با ما و قوانین را مدیریت کن.</p>
				<span class="kadad-cms-card-link">رفتن به برگه‌ها <span aria-hidden="true">←</span></span>
			</a>
			<a class="kadad-cms-card" href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>">
				<span class="kadad-cms-card-icon">▧</span>
				<h3>رسانه‌ها</h3><p>تصاویر و فایل‌های موردنیاز سایت را از یک جا مدیریت کن.</p>
				<span class="kadad-cms-card-link">مدیریت رسانه‌ها <span aria-hidden="true">←</span></span>
			</a>
		</section>

		<section class="kadad-cms-notice">
			<span class="kadad-cms-notice-icon">✦</span>
			<div><h2>این فقط شروع ماجراست</h2><p>نسخه ۰.۰.۱ زیرساخت اولیه مدیریت محصولات را فراهم می‌کند. سبد خرید، پرداخت، سفارش‌ها و قالب فروشگاه هنوز پیاده‌سازی نشده‌اند و در مراحل بعدی ساخته می‌شوند.</p></div>
		</section>
		<p class="kadad-cms-footer">ساخته‌شده برای تجربه‌ای ساده‌تر و حرفه‌ای‌تر · Kadad CMS</p>
	</div>
	<?php
}

/**
 * Load scoped styles only on Kadad CMS admin screens.
 */
function kadad_cms_admin_styles( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$is_kadad_page = ( 'toplevel_page_kadad-cms' === $hook )
		|| ( $screen && in_array( $screen->post_type, array( 'kadad_product' ), true ) );

	if ( ! $is_kadad_page ) {
		return;
	}

	$css = '
		.kadad-cms-admin { max-width: 1320px; margin: 22px 18px 0 0; color: #172033; }
		.kadad-cms-admin * { box-sizing: border-box; }
		.kadad-cms-hero { display:flex; align-items:center; justify-content:space-between; gap:24px; padding:30px; border-radius:20px; color:#fff; background:linear-gradient(120deg,#111d3a 0%,#293c72 58%,#6256d9 100%); box-shadow:0 12px 34px rgba(32,45,94,.16); }
		.kadad-cms-brand { display:flex; align-items:center; gap:18px; }
		.kadad-cms-mark { display:grid; place-items:center; flex:0 0 62px; height:62px; border:1px solid rgba(255,255,255,.3); border-radius:18px; background:rgba(255,255,255,.12); font-size:32px; font-weight:800; }
		.kadad-cms-eyebrow { margin:0 0 4px; color:#c9d3ff; font-size:12px; letter-spacing:.04em; }
		.kadad-cms-hero h1 { margin:0 0 7px; color:#fff; font-size:27px; font-weight:750; }
		.kadad-cms-version { display:inline-block; padding:4px 8px; border-radius:8px; background:rgba(255,255,255,.13); color:#e2e7ff; font-size:11px; vertical-align:middle; }
		.kadad-cms-hero p:last-child { margin:0; color:#e0e6ff; font-size:13px; }
		.kadad-cms-primary { display:inline-flex; align-items:center; justify-content:center; white-space:nowrap; padding:12px 18px; border-radius:12px; background:#fff; color:#242f63; text-decoration:none; font-weight:700; box-shadow:0 4px 12px rgba(0,0,0,.08); }
		.kadad-cms-primary:hover { background:#f0f2ff; color:#111d3a; }
		.kadad-cms-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; margin:20px 0 30px; }
		.kadad-cms-stat { display:flex; align-items:center; gap:14px; min-height:100px; padding:20px; border:1px solid #e8ebf2; border-radius:16px; background:#fff; }
		.kadad-cms-stat-icon { display:grid; place-items:center; width:46px; height:46px; border-radius:14px; background:#f0f1ff; color:#5a55cb; font-size:22px; }
		.kadad-cms-stat div { display:grid; gap:6px; }
		.kadad-cms-stat span:not(.kadad-cms-stat-icon) { color:#737b8e; font-size:12px; }
		.kadad-cms-stat strong { color:#172033; font-size:25px; line-height:1; }
		.kadad-cms-section-heading { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
		.kadad-cms-section-heading h2 { margin:0 0 5px; font-size:19px; color:#172033; }
		.kadad-cms-section-heading p { margin:0; color:#7a8294; font-size:12px; }
		.kadad-cms-cards { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:15px; }
		.kadad-cms-card { display:flex; flex-direction:column; align-items:flex-start; min-height:205px; padding:21px; border:1px solid #e8ebf2; border-radius:16px; background:#fff; color:#172033; text-decoration:none; transition:transform .16s ease,border-color .16s ease,box-shadow .16s ease; }
		.kadad-cms-card:hover { transform:translateY(-3px); border-color:#c9caff; box-shadow:0 10px 28px rgba(36,47,99,.08); color:#172033; }
		.kadad-cms-card-icon { display:grid; place-items:center; width:43px; height:43px; border-radius:13px; background:#f2f3ff; font-size:20px; }
		.kadad-cms-card h3 { margin:15px 0 7px; font-size:15px; }
		.kadad-cms-card p { margin:0 0 18px; color:#737b8e; font-size:12px; line-height:1.9; }
		.kadad-cms-card-link { display:flex; align-items:center; gap:8px; margin-top:auto; color:#5550c7; font-size:12px; font-weight:700; }
		.kadad-cms-notice { display:flex; align-items:flex-start; gap:14px; margin-top:22px; padding:21px; border:1px solid #e4e5ff; border-radius:16px; background:#f7f7ff; }
		.kadad-cms-notice-icon { color:#5c56d8; font-size:24px; }
		.kadad-cms-notice h2 { margin:0 0 7px; font-size:15px; }
		.kadad-cms-notice p { margin:0; color:#697188; font-size:12px; line-height:1.9; }
		.kadad-cms-footer { margin:22px 0; color:#9aa1b0; font-size:11px; text-align:center; }
		.kadad-cms-fields { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
		.kadad-cms-fields p { margin:0; }
		.kadad-cms-fields label { display:block; margin-bottom:8px; }
		.kadad-cms-fields .description { display:block; margin-top:6px; line-height:1.8; }
		@media(max-width:1100px) { .kadad-cms-cards { grid-template-columns:repeat(2,minmax(0,1fr)); } }
		@media(max-width:782px) { .kadad-cms-admin { margin-right:0; } .kadad-cms-hero { align-items:flex-start; flex-direction:column; padding:22px; } .kadad-cms-stats { grid-template-columns:1fr; } .kadad-cms-cards { grid-template-columns:1fr; } .kadad-cms-fields { grid-template-columns:1fr; } }
		@media(prefers-reduced-motion:reduce) { .kadad-cms-card { transition:none; } .kadad-cms-card:hover { transform:none; } }
	';

	wp_register_style( 'kadad-cms-admin', false, array(), KADAD_CMS_VERSION );
	wp_enqueue_style( 'kadad-cms-admin' );
	wp_add_inline_style( 'kadad-cms-admin', $css );
}
add_action( 'admin_enqueue_scripts', 'kadad_cms_admin_styles' );

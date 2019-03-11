<?php
/**
 * JavaScript や CSS を読み込みます。
 */
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
  wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
  wp_enqueue_script(
    'twentynineteen-child-touch-navigation',
    get_stylesheet_directory_uri() . '/js/touch-keyboard-navigation.js',
    array('twentynineteen-priority-menu'),
    false,
    true
  );
}

/**
 * 親テーマの JavaScript ファイルをデキューします。
 *
 * Hooked to the wp_print_scripts action, with a late priority (100),
 * so that it is after the script was enqueued.
 */
function wpdocs_dequeue_script() {
   wp_dequeue_script( 'twentynineteen-touch-navigation' );
}
add_action( 'wp_print_scripts', 'wpdocs_dequeue_script', 100 );

/**
 * more タグで URL 末端に付く #more-xxxx を削除します。
 */
function remove_more_link_scroll( $link ) {
  $link = preg_replace( '|#more-[0-9]+|', '', $link );
  return $link;
}
add_filter( 'the_content_more_link', 'remove_more_link_scroll' );

/**
 * メニューに検索フォームを追加します。
 */
function add_search_box_to_menu( $items, $args ) {
  //var_dump($args);
  if( $args->theme_location === 'menu-1' ){
    return $items . '<li>' . get_search_form(false) . '</li>';
  }
}
add_filter( 'wp_nav_menu_items', 'add_search_box_to_menu', 10, 2);

/**
 * テーマカスタマイザーの、色 > アイキャッチ画像にメインカラーのフィルターを適用する
 * のチェックなしをデフォルトにします。
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function child_twentynineteen_customize_register( $wp_customize ) {
  // Add image filter setting and control.
  $wp_customize->add_setting(
    'image_filter',
    array(
      'default'           => 0,
      'sanitize_callback' => 'absint',
      'transport'         => 'postMessage',
    )
  );
}
add_action( 'customize_register', 'child_twentynineteen_customize_register', 100 );

/**
 * デフォルトのサイトアイコンを設定します。
 * カスタマイザーで設定された場合はそちらを使用します。
 */
function filter_site_icon_meta_tags() {
  if (has_site_icon()) {
    return;
  }

  $url = get_stylesheet_directory_uri();
  // 出力される HTML ソースコードを見やすくするめに、最後に空白行を設置
  echo <<<EOT
<link rel="icon" href="{$url}/images/cropped-site_icon-32x32.jpg" sizes="32x32" />
<link rel="icon" href="{$url}/images/cropped-site_icon-192x192.jpg" sizes="192x192" />
<link rel="apple-touch-icon-precomposed" href="{$url}/images/cropped-site_icon-180x180.jpg" />
<meta name="msapplication-TileImage" content="{$url}/images/cropped-site_icon-270x270.jpg" />

EOT;
}
add_filter('wp_head', 'filter_site_icon_meta_tags');

/**
 * デフォルトのテーマロゴを出力します。
 */
function the_theme_logo() {
  $url = home_url();
  $theme_url = get_stylesheet_directory_uri();
  echo <<<EOT
<a href="{$url}/" class="custom-logo-link" rel="home" itemprop="url"><img width="190" height="190" src="{$theme_url}/images/theme_logo.jpg" class="custom-logo" alt="test" itemprop="logo" srcset="{$theme_url}/images/theme_logo.jpg 190w, {$theme_url}/theme_logo-150x150.jpg 150w" sizes="(max-width: 34.9rem) calc(100vw - 2rem), (max-width: 53rem) calc(8 * (100vw / 12)), (min-width: 53rem) calc(6 * (100vw / 12)), 100vw" /></a>
EOT;
}

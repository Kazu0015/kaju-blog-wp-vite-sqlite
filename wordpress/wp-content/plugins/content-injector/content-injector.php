<?php
/*
Plugin Name: Content Injector
Description: 投稿の最後にコンテンツを追加するプラグイン
Version: 1.0
*/

add_filter('the_content', function($content) {

  if (!is_singular()) return $content;

  return $content . '<div class="ci-box">テスト：追加されたコンテンツ</div>';
});

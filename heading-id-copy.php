<?php

/**
 * Plugin Name: Heading ID Copy
 * Description: Add ID to heading tags and allow copying URL with the heading ID.
 * Version: 1.0
 * Author: 김철준
 * Author URI: https://devcj.kr
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Direct access not allowed
}

// Add ID to heading tags (h2 to h6) with wp-block-heading class
function add_id_to_heading_tags($content) {
    $content = preg_replace_callback(
        '/<h([2-6]) class="wp-block-heading">(.*?)<\/h\1>/iu',
        function ($matches) {
            $tag = $matches[1];
            $text = $matches[2];
            $id_base = preg_replace('/\s+/u', '-', trim($text));
            $id_base = preg_replace('/[^\p{L}\p{N}-]+/u', '', $id_base);

            static $id_counter = [];
            if (isset($id_counter[$id_base])) {
                $id_counter[$id_base]++;
                $id = $id_base . '-' . $id_counter[$id_base];
            } else {
                $id_counter[$id_base] = 1;
                $id = $id_base;
            }

            return "<h{$tag} class=\"wp-block-heading\" id=\"{$id}\" onclick=\"copyToClipboard('{$id}')\">{$text}</h{$tag}>";
        },
        $content
    );

    return $content;
}
add_filter('the_content', 'add_id_to_heading_tags');

// Add custom styles for cursor: pointer on wp-block-heading
function add_custom_styles() {
    ?>
    <style>
        .wp-block-heading {
            cursor: pointer;
        }
    </style>
    <?php
}
add_action('wp_head', 'add_custom_styles');

// Add the copy to clipboard JavaScript function inline
function add_copy_to_clipboard_script() {
    ?>
    <script>
        function copyToClipboard(id) {
            // 현재 URL을 가져오고 마지막 '/'가 있으면 제거
            let url = window.location.href.split('#')[0]; // #을 기준으로 현재 URL 분리
            if (url.endsWith('/')) {
                url = url.slice(0, -1); // 마지막 '/' 제거
            }

            // URL에 헤딩의 id를 추가
            url = url + '#' + id;

            // 임시로 텍스트 영역을 생성하여 복사
            const textArea = document.createElement('textarea');
            textArea.value = url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');  // 복사 명령 실행
            document.body.removeChild(textArea);

            // 언어에 맞는 메시지 설정
            let language = navigator.language || navigator.userLanguage; // 브라우저 언어 가져오기
            let message = '';

            if (language.startsWith('ko')) {
                message = 'URL이 복사되었습니다';
            } else {
                message = 'URL has been copied';
            }

            // 사용자에게 복사됨 알림
            alert(message);
        }
    </script>
    <?php
}
add_action('wp_footer', 'add_copy_to_clipboard_script');

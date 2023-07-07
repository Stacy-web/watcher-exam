<?php
/*
Plugin Name: Exam Watcher
Version    : 1.0
*/

register_activation_hook(__FILE__, function () {
    $token = wp_generate_password(20, false);
    update_option('exam_watcher_plugin_token', $token);

    create_custom_page();
});

add_action('init', function () {
    add_shortcode('exam_watcher_plugin_page', function () {
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';

        $valid_token = get_option('exam_watcher_plugin_token');
        if ($token !== $valid_token) {
            echo 'Доступ запрещен!';
            return;
        }

        $users = get_users([
            'role__in' => array(
                'temporary_user_8707',
                'temporary_user_9151',
                'temporary_user_10146'
            ),
            'meta_key' => 'acc_in_processing',
            'meta_value' => 1
        ]);

        $directory = [
            8707 => [
                'name' => 'Проектный тренажёр',
                'categories' => [27]
            ],
            9151 => [
                'name' => 'Строительный тренажёр',
                'categories' => [30, 26]
            ],
            10146 => [
                'name' => 'Строительный тренажёр',
                'categories' => [28, 29]
            ]
        ];

        echo "<pre>";
        print_r($users);
        echo "</pre>";

        echo "<select>";
        foreach ($users as $user) {
            $userRole = $user->roles[0];
            $trainer = $directory[str_replace('temporary_user_', '', $userRole)]['name'];
            echo "<option value='{$user->ID}'>{$user->data->display_name} — {$trainer}</option>";
        }
        echo "</select>";

        global $wpdb;



        $question = $wpdb->get_results( 'SELECT questions.id, questions.question, answers.answer, answers.correct FROM aspks_aysquiz_answers as answers LEFT JOIN aspks_aysquiz_questions questions ON answers.question_id = questions.id', 'ARRAY_A' );

        $res = [];
        foreach ($question as $question) {
            $res[$question['id']]['question'] = $question['question'];
            $res[$question['id']]['answers'][] = array(
                'answer' => $question['answer'],
                'correct' => $question['correct']
            );
        }

        echo "<pre>";
        print_r($res);
        echo "</pre>";
    });
});


function create_custom_page() {
    $page_title = 'My Custom Page';
    $page_content = '[exam_watcher_plugin_page]';
    $page_slug = 'my-custom-page'; 

    $page = get_page_by_path($page_slug);

    if (!$page) {
        $page_data = array(
            'post_title'    => $page_title,
            'post_content'  => $page_content,
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => $page_slug
        );

        wp_insert_post($page_data);
    }
}
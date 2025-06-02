<?php
add_action('rest_api_init', function () {
    register_rest_route('learnpress/v1', '/courses', [
        'methods'             => 'GET',
        'callback'            => 'custom_learnpress_courses',
        'permission_callback' => '__return_true',
    ]);
});

function custom_learnpress_courses($request)
{
    $tags     = $request->get_param('tags');
    $search   = $request->get_param('search');
    $page     = isset($request['page']) ? max(1, intval($request['page'])) : 1;
    $per_page = isset($request['per_page']) ? intval($request['per_page']) : 9;
    $offset   = ($page - 1) * $per_page;

                                                 // Ambil filter category dan price dari request
    $category = $request->get_param('category'); // contoh: slug kategori
    $price    = $request->get_param('price');    // contoh: "min-max" => "100000-500000"

    $args = [
        'post_type'      => 'lp_course',
        'posts_per_page' => $per_page,
        'offset'         => $offset,
        'post_status'    => 'publish',
    ];

    if (! empty($search)) {
        $args['s'] = sanitize_text_field($search);
    }

    // Filter kategori jika ada
    if (! empty($category)) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'course_category', // pastikan taxonomy ini sesuai dengan LearnPress kategori
                'field'    => 'slug',
                'terms'    => explode(',', sanitize_text_field($category)), // bisa multiple kategori dengan koma
            ],
        ];
    }

    // Filter price jika ada
    if (! empty($price)) {
        // Misal price dikirim "min-max" seperti "100000-500000"
        $prices    = explode('-', $price);
        $min_price = isset($prices[0]) ? floatval($prices[0]) : 0;
        $max_price = isset($prices[1]) ? floatval($prices[1]) : PHP_INT_MAX;

        // Meta query untuk filter harga
        $args['meta_query'][] = [
            'key'     => '_lp_price',
            'value'   => [$min_price, $max_price],
            'compare' => 'BETWEEN',
            'type'    => 'NUMERIC',
        ];
    }

    if (! empty($tags)) {
        $tag_slugs           = array_map('sanitize_title', explode(',', $tags));
        $args['tax_query'][] = [
            'taxonomy' => 'post_tag',
            'field'    => 'slug',
            'terms'    => $tag_slugs,
            'operator' => 'IN',
        ];
    }

    $query = new WP_Query($args);
    $posts = $query->posts;
    $total = $query->found_posts;

    $data = [];

    $meta_keys = [
        '_lp_duration', '_lp_block_expire_duration', '_lp_block_finished',
        '_lp_allow_course_repurchase', '_lp_course_repurchase_option',
        '_lp_level', '_lp_students', '_lp_max_students', '_lp_retake_count',
        '_lp_has_finish', '_lp_featured', '_lp_featured_review',
        '_lp_external_link_buy_course', '_lp_course_review_enable',
        '_lp_hide_students_list', '_lp_offline_course', '_lp_offline_lesson_count',
        '_lp_deliver_type', '_lp_address', '_lp_regular_price', '_lp_sale_price',
        '_lp_sale_start', '_lp_sale_end', '_lp_price_prefix', '_lp_price_suffix',
        '_lp_no_required_enroll', '_lp_requirements', '_lp_target_audiences',
        '_lp_key_features', '_lp_faqs', '_lp_course_result', '_lp_passing_condition',
        'post_author', '_lp_course_material', '_lp_bbpress_forum_enable',
        '_lp_course_forum', '_lp_bbpress_forum_enrolled_user', '_lp_coming_soon',
        '_lp_coming_soon_msg', '_lp_coming_soon_end_time', '_lp_coming_soon_countdown',
        '_lp_coming_soon_showtext', '_lp_coming_soon_metadata', '_lp_coming_soon_details',
    ];

    foreach ($posts as $post) {
        $course_id = $post->ID;
        $meta_data = [];

        foreach ($meta_keys as $key) {
            if ($key === 'post_author') {
                $meta_data[$key] = get_post_field('post_author', $course_id);
            } else {
                $value           = get_post_meta($course_id, $key, true);
                $meta_data[$key] = $value !== '' ? maybe_unserialize($value) : (in_array($key, ['_lp_requirements', '_lp_target_audiences', '_lp_key_features', '_lp_faqs']) ? [] : '');
            }
        }

        $author_id = get_post_field('post_author', $course_id);
        $author    = get_userdata($author_id);

        $image_url = get_the_post_thumbnail_url($course_id, 'full');
        if (! $image_url) {
            $image_url = plugins_url('learnpress/assets/images/no-image.png');
        }

        $price        = floatval(get_post_meta($course_id, '_lp_price', true));
        $origin_price = get_post_meta($course_id, '_lp_regular_price', true);
        $on_sale      = ! empty($origin_price) && $price < floatval($origin_price);

        $rating_info         = get_course_rating_info($course_id);
        $curriculumDetail    = get_curriculum_item_breakdown($course_id);
        $totalUserJoinCourse = get_total_users_joined_course($course_id);
        $data[]              = [
            'id'                    => $course_id,
            'name'                  => get_the_title($course_id),
            'image'                 => $image_url,
            'instructor'            => [
                'avatar'      => get_avatar_url($author_id),
                'id'          => $author_id,
                'name'        => $author->display_name ?? '',
                'description' => get_user_meta($author_id, 'description', true) ?? '',
            ],
            'duration'              => $meta_data['_lp_duration'],
            'categories'            => [],
            'price'                 => $price,
            'price_rendered'        => $price ? 'Rp' . number_format($price, 2, ',', '.') : 'Free',
            'origin_price'          => $origin_price ?: '',
            'origin_price_rendered' => 'Rp' . number_format($origin_price ?: 0, 2, ',', '.'),
            'on_sale'               => $on_sale,
            'sale_price'            => $on_sale ? $price : 0,
            'sale_price_rendered'   => 'Rp' . number_format($on_sale ? $price : 0, 2, ',', '.'),
            'rating'                => $rating_info['average'],
            'review_count'          => $rating_info['count'],
            'user_join'             => $totalUserJoinCourse,
            'curriculum'            => $curriculumDetail,
            'meta_data'             => $meta_data,
        ];
    }

    $response = rest_ensure_response($data);

    // Pakai $total dari WP_Query bukan wp_count_posts yang total keseluruhan tanpa filter
    $response->header('X-WP-Total', intval($total));
    $response->header('X-WP-TotalPages', ceil($total / $per_page));

    return $response;
}

function get_course_rating_info($course_id)
{
    global $wpdb;

    // Ambil semua rating yang valid dari komentar yang sudah disetujui dan bertipe 'review'
    $sql = $wpdb->prepare("SELECT cm.comment_id, cm.meta_value
FROM {$wpdb->commentmeta} cm
INNER JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID
WHERE c.comment_post_ID = $course_id AND cm.meta_key = '_lpr_rating'");

    // echo $sql;
    // die;
    $results = $wpdb->get_results($sql);

    $total_rating    = 0;
    $total_reviewers = 0;

    foreach ($results as $row) {
        $rating = $row->meta_value;
        if (is_numeric($rating) && $rating > 0) {
            $total_rating += floatval($rating);
            $total_reviewers++;
        }
    }

    $average = $total_reviewers > 0 ? round($total_rating / $total_reviewers, 2) : 0;

    return [
        'average' => $average,
        'count'   => $total_reviewers,
    ];
}

function get_curriculum_item_breakdown($course_id)
{
    global $wpdb;

    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT json FROM {$wpdb->prefix}learnpress_courses WHERE ID = %d",
        $course_id
    ));

    if ($row && isset($row->json)) {
        $data = json_decode($row->json, true);

        if (is_array($data) && isset($data['total_items'])) {
            return [
                'total'  => intval($data['total_items']['count_items'] ?? 0),
                'lesson' => intval($data['total_items']['lp_lesson'] ?? 0),
                'quiz'   => intval($data['total_items']['lp_quiz'] ?? 0),
            ];
        }
    }

    return [
        'total'  => 0,
        'lesson' => 0,
        'quiz'   => 0,
    ];
}

function get_total_users_joined_course($course_id)
{
    global $wpdb;

    $real_count = (int) $wpdb->get_var(
        $wpdb->prepare("
            SELECT COUNT(DISTINCT user_id)
            FROM {$wpdb->prefix}learnpress_user_items
            WHERE item_id = %d AND item_type = 'lp_course'
        ", $course_id)
    );

    $fake_count = (int) get_post_meta($course_id, '_lp_students', true);

    $total = $real_count + $fake_count;
    return intval($total);
}

<?php
function se_event_list_shortcode() {
    $args = [
        'post_type' => 'event',
        'posts_per_page' => -1, // tampilkan semua event
        'orderby' => 'meta_value',
        'meta_key' => '_se_event_date',
        'order' => 'ASC',
        'meta_query' => [
            [
                'key' => '_se_event_date',
                'compare' => 'EXISTS'
            ]
        ],
        'post_status' => 'publish',
        'ignore_sticky_posts' => true
    ];

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()) {
        echo '<div class="se-event-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">';
        while ($query->have_posts()) {
            $query->the_post();
            $date = get_post_meta(get_the_ID(), '_se_event_date', true);
            $location = get_post_meta(get_the_ID(), '_se_event_location', true);
            $thumbnail = get_the_post_thumbnail(get_the_ID(), 'medium', [
                'style' => 'width:100%; height:180px; object-fit:cover; display:block;'
            ]);
            ?>
            <div class="se-event-card" style="border: 1px solid #eee; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.08); background: #fff;">
                <?php if ($thumbnail) : ?>
                    <div class="se-event-thumbnail">
                        <?php echo $thumbnail; ?>
                    </div>
                <?php endif; ?>
                <div class="se-event-content" style="padding: 15px;">
                    <h3 style="margin-top: 0; font-size: 1.1em;"><?php the_title(); ?></h3>
                    <p style="margin: 5px 0;"><strong>Tanggal:</strong> <?php echo esc_html($date); ?></p>
                    <p style="margin: 5px 0;"><strong>Lokasi:</strong> <?php echo esc_html($location); ?></p>
                    <a href="<?php the_permalink(); ?>" style="display: inline-block; margin-top: 10px; color: #0073aa;">Lihat Detail</a>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    } else {
        echo '<p>Belum ada event.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('event_list', 'se_event_list_shortcode');

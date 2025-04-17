<?php
get_header();

if (have_posts()) :
    while (have_posts()) : the_post();
    // Get event meta data
    $event_start_date = get_post_meta(get_the_ID(), '_se_event_start_date', true);
    $event_start_time = get_post_meta(get_the_ID(), '_se_event_start_time', true);
    $event_end_date = get_post_meta(get_the_ID(), '_se_event_end_date', true);
    $event_end_time = get_post_meta(get_the_ID(), '_se_event_end_time', true);
    $event_location = get_post_meta(get_the_ID(), '_se_event_location', true);
    $event_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
    
    // Check if event has ended
    $is_event_ended = false;
    $current_datetime = current_time('timestamp');
    
    // Set default end time if not available
    if (empty($event_end_time)) $event_end_time = '23:59';
    
    // Create timestamp for end date
    $event_end_timestamp = strtotime($event_end_date . ' ' . $event_end_time);
    
    // If end date is empty, use start date
    if (empty($event_end_date) && !empty($event_start_date)) {
        $event_end_date = $event_start_date;
        $event_end_timestamp = strtotime($event_start_date . ' ' . $event_end_time);
    }
    
    // Check if event has ended
    if (!empty($event_end_timestamp) && $current_datetime > $event_end_timestamp) {
        $is_event_ended = true;
    }
    
    // Format display date
    $display_date = !empty($event_start_date) ? date_i18n('l, d F Y', strtotime($event_start_date)) : '';
?>

<!-- Add CSS for Popup -->
<style>
    .popup-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    
    .popup-container {
        background-color: white;
        padding: 2rem;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
    }
    
    .popup-close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 24px;
        cursor: pointer;
        color: #333;
    }
    
    .reservation-button {
        background-color: #EA242A;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        margin-top: 1rem;
        display: inline-block;
        text-decoration: none;
        font-weight: bold;
    }
    
    .reservation-button:hover {
        background-color: #c01e23;
    }
    
    .event-ended-button {
        background-color: #888888;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: not-allowed;
        margin-top: 1rem;
        display: inline-block;
        text-decoration: none;
        font-weight: bold;
    }
    .event-ended-button:hover {
        background-color: #888888;
    }
</style>

<div style="max-width: 1000px; margin: 0 auto; padding: 2rem;">
    <!-- Banner -->
    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
        <?php if ($event_image): ?>
            <div style="flex: 1 1 300px;">
                <img src="<?php echo esc_url($event_image); ?>" alt="<?php the_title(); ?>" style="width: 100%; border-radius: 8px;">
                <!-- Share buttons -->
                <div style="margin-top: 1rem;">
                    <p style="font-weight: bold;">Bagikan Event:</p>
                    <?php
                        $share_url = urlencode(get_permalink());
                        $share_title = urlencode(get_the_title());
                    ?>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" style="margin-right: 10px; text-decoration: none;"><span class="dashicons dashicons-facebook"></span> Facebook</a>
                    <a href="https://www.instagram.com/?url=<?php echo $share_url; ?>" target="_blank" style="text-decoration: none;"><span class="dashicons dashicons-instagram"></span> Instagram</a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $share_url; ?>" target="_blank" style="margin-right: 10px; text-decoration: none;"><span class="dashicons dashicons-linkedin"></span> LinkedIn</a>
                    <a href="https://wa.me/?text=<?php echo $share_title . '%20' . $share_url; ?>" target="_blank" style="text-decoration: none;"><span class="dashicons dashicons-whatsapp"></span> WhatsApp</a>
                </div>
            </div>
        <?php endif; ?>

        <div style="flex: 2 1 500px;">
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php the_title(); ?></h1>
            <h2 style="font-size: 1.5rem; margin-bottom: 1rem;">Detail Event</h2>
            <div style="background-color: #f9f9f9; padding: 1rem; border-radius: 8px;">
                <p><strong>📅 Tanggal:</strong> <?php echo esc_html($display_date); ?></p>
                <p><strong>🕒 Jam:</strong> <?php echo esc_html($event_start_time); ?> - <?php echo esc_html($event_end_time); ?></p>
                <p><strong>📍 Lokasi:</strong> <?php echo esc_html($event_location); ?></p>
            </div>
            
            <!-- Conditional Button based on event date -->
            <?php if ($is_event_ended): ?>
                <button class="event-ended-button" disabled>Acara Sudah Selesai</button>
            <?php else: ?>
                <button class="reservation-button" id="reservationBtn">Reservasi Sekarang</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Deskripsi  -->
    <div style="margin-top: 3rem;"></div>
        <h2 style="font-size: 1.5rem; margin-top: 2rem;">Deskripsi Event</h2>
        <div style="background-color: #f9f9f9; padding: 1rem; border-radius: 8px;">
            <p> <?php the_content(); ?></p>
        </div>
        
        <!-- Content -->
        <h2 style="font-size: 1.5rem; margin-top: 2rem;">Pendaftaran</h2>
        <div style="background-color: #f9f9f9; padding: 1rem; border-radius: 8px;">
            <?php if ($is_event_ended): ?>
                <p>Acara ini telah berakhir dan pendaftaran sudah ditutup.</p>
            <?php else: ?>
                <p>Untuk mendaftar, silakan klik tombol Reservasi Sekarang di atas.</p>
                <p>Jika Anda sudah mendaftar, silakan cek email Anda untuk konfirmasi pendaftaran.</p>
            <?php endif; ?>  
        </div>
    </div>
</div>

<!-- Popup Form - Only show if event hasn't ended -->
<?php if (!$is_event_ended): ?>
<div class="popup-overlay" id="reservationPopup">
    <div class="popup-container">
        <span class="popup-close" id="popupClose">&times;</span>
        <h3 style="text-align: center; margin-bottom: 1.5rem;">Formulir Pendaftaran</h3>
        <h5 style="text-align: center; margin-bottom: 1.5rem;"><?php the_title(); ?></h5>
        <?php echo do_shortcode('[event_registration_form id="' . get_the_ID() . '"]'); ?>
    </div>
</div>

<!-- JavaScript for Popup Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reservationBtn = document.getElementById('reservationBtn');
    const reservationPopup = document.getElementById('reservationPopup');
    const popupClose = document.getElementById('popupClose');
    
    // Open popup when reservation button is clicked
    if (reservationBtn) {
        reservationBtn.addEventListener('click', function() {
            reservationPopup.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling when popup is open
        });
    }
    
    // Close popup when X is clicked
    if (popupClose) {
        popupClose.addEventListener('click', function() {
            reservationPopup.style.display = 'none';
            document.body.style.overflow = 'auto'; // Enable scrolling again
        });
    }
    
    // Close popup when clicking outside the form
    if (reservationPopup) {
        reservationPopup.addEventListener('click', function(event) {
            if (event.target === reservationPopup) {
                reservationPopup.style.display = 'none';
                document.body.style.overflow = 'auto'; // Enable scrolling again
            }
        });
    }
});
</script>
<?php endif; ?>

<?php
    endwhile;
endif;

get_footer();
?>
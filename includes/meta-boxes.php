<?php
// Helper: Default form fields
function se_get_default_form_fields() {
    return [
        ['key' => 'name',      'label' => 'Nama',      'type' => 'text',  'required' => true],
        ['key' => 'email',     'label' => 'Email',      'type' => 'email', 'required' => true],
        ['key' => 'phone',     'label' => 'No HP',      'type' => 'tel',   'required' => true],
        ['key' => 'company',   'label' => 'Company',    'type' => 'text',  'required' => false],
        ['key' => 'job_title', 'label' => 'Job Title',  'type' => 'text',  'required' => false],
    ];
}

// Helper: Get form fields for an event (fallback to defaults)
function se_get_event_form_fields($event_id) {
    $fields = get_post_meta($event_id, '_se_event_form_fields', true);
    if (!is_array($fields) || empty($fields)) {
        return se_get_default_form_fields();
    }
    return $fields;
}

// Tambahkan meta box ke post type Event
function se_add_event_meta_boxes() {
    add_meta_box('se_event_details', 'Detail Event', 'se_render_event_meta_box', 'event', 'normal', 'default');
    add_meta_box('se_event_speakers', 'Pembicara & Moderator', 'se_render_speakers_meta_box', 'event', 'normal', 'default');
    add_meta_box('se_event_target_audience', 'Target Audience', 'se_render_target_audience_meta_box', 'event', 'normal', 'default');
    add_meta_box('se_event_form_fields', 'Konfigurasi Field Formulir', 'se_render_form_fields_meta_box', 'event', 'normal', 'default');
}
add_action('add_meta_boxes', 'se_add_event_meta_boxes');

// Enqueue media uploader di halaman edit event
function se_enqueue_admin_scripts($hook) {
    global $post_type;
    if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'event') {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'se_enqueue_admin_scripts');

// Render isi meta box
function se_render_event_meta_box($post) {
    // Get existing values
    $start_date = get_post_meta($post->ID, '_se_event_start_date', true);
    $start_time = get_post_meta($post->ID, '_se_event_start_time', true);
    $end_date = get_post_meta($post->ID, '_se_event_end_date', true);
    $end_time = get_post_meta($post->ID, '_se_event_end_time', true);
    $location = get_post_meta($post->ID, '_se_event_location', true);
    $quota = get_post_meta($post->ID, '_se_event_quota', true);
    $replay_url = get_post_meta($post->ID, '_se_event_replay_url', true);
    $google_form_url = get_post_meta($post->ID, '_se_event_google_form_url', true);
    $form_title = get_post_meta($post->ID, '_se_event_form_title', true);
    $form_subtitle = get_post_meta($post->ID, '_se_event_form_subtitle', true);

    // Set default time values if empty
    if (empty($start_time)) $start_time = '09:00';
    if (empty($end_time)) $end_time = '17:00';
    
    // Add nonce for security
    wp_nonce_field('se_event_meta_nonce', 'se_event_meta_nonce');
    ?>
    <div class="se-meta-section">
        <p><label for="se_event_start_date"><strong>Start Date:</strong></label><br>
        <input type="date" id="se_event_start_date" name="se_event_start_date" value="<?php echo esc_attr($start_date); ?>" required>
        <label for="se_event_start_time" style="margin-left: 10px;"><strong>Jam Mulai:</strong></label>
        <input type="time" id="se_event_start_time" name="se_event_start_time" value="<?php echo esc_attr($start_time); ?>" required></p>

        <p><label for="se_event_end_date"><strong>End Date:</strong></label><br>
        <input type="date" id="se_event_end_date" name="se_event_end_date" value="<?php echo esc_attr($end_date); ?>" required>
        <label for="se_event_end_time" style="margin-left: 10px;"><strong>Jam Selesai:</strong></label>
        <input type="time" id="se_event_end_time" name="se_event_end_time" value="<?php echo esc_attr($end_time); ?>" required></p>

        <p><label for="se_event_location"><strong>Lokasi:</strong></label><br>
        <input type="text" id="se_event_location" name="se_event_location" value="<?php echo esc_attr($location); ?>" style="width:100%;"></p>

        <p><label for="se_event_quota"><strong>Kuota Maksimal:</strong></label><br>
        <input type="number" id="se_event_quota" name="se_event_quota" value="<?php echo esc_attr($quota); ?>" min="1"></p>

        <hr style="margin: 15px 0;">
        <p><label for="se_event_replay_url"><strong>YouTube Replay URL:</strong></label><br>
        <input type="url" id="se_event_replay_url" name="se_event_replay_url" value="<?php echo esc_attr($replay_url); ?>" style="width:100%;" placeholder="https://www.youtube.com/watch?v=...">
        <small style="color:#666;">Isi URL video YouTube untuk replay event. Video hanya tampil setelah event selesai.</small></p>

        <hr style="margin: 15px 0;">
        <p><label for="se_event_google_form_url"><strong>Google Form URL (Pendaftaran):</strong></label><br>
        <input type="url" id="se_event_google_form_url" name="se_event_google_form_url" value="<?php echo esc_attr($google_form_url); ?>" style="width:100%;" placeholder="https://docs.google.com/forms/d/e/FORM_ID/viewform">
        <small style="color:#666;">Jika diisi, formulir pendaftaran akan menggunakan embed Google Form. Kosongkan untuk menggunakan formulir bawaan website. <strong>Hanya berlaku untuk registrasi, bukan replay.</strong></small></p>

        <hr style="margin: 15px 0;">
        <p><label for="se_event_form_title"><strong>Judul Formulir (Custom):</strong></label><br>
        <input type="text" id="se_event_form_title" name="se_event_form_title" value="<?php echo esc_attr($form_title); ?>" style="width:100%;" placeholder="Contoh: Jangan Lewatkan Sesi Eksklusif Ini!">
        <small style="color:#666;">Judul besar yang tampil di atas formulir pendaftaran. Kosongkan untuk default.</small></p>

        <p><label for="se_event_form_subtitle"><strong>Subtitle Formulir (Custom):</strong></label><br>
        <textarea id="se_event_form_subtitle" name="se_event_form_subtitle" style="width:100%; height:60px;" placeholder="Contoh: Kesempatan bagi Anda yang membutuhkan IT Talent..."><?php echo esc_textarea($form_subtitle); ?></textarea>
        <small style="color:#666;">Deskripsi singkat di bawah judul formulir. Kosongkan untuk default.</small></p>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get form elements
        const startDateInput = document.getElementById('se_event_start_date');
        const startTimeInput = document.getElementById('se_event_start_time');
        const endDateInput = document.getElementById('se_event_end_date');
        const endTimeInput = document.getElementById('se_event_end_time');
        
        // Function to validate dates
        function validateDates() {
            const startDate = new Date(`${startDateInput.value}T${startTimeInput.value}`);
            const endDate = new Date(`${endDateInput.value}T${endTimeInput.value}`);
            
            if (endDate < startDate) {
                alert('Error: Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
                return false;
            }
            return true;
        }
        
        // Set up validation when the form is submitted
        const form = document.querySelector('#post');
        form.addEventListener('submit', function(e) {
            if (!validateDates()) {
                e.preventDefault();
            }
        });
        
        // Set up validation when end date or time changes
        endDateInput.addEventListener('change', validateDates);
        endTimeInput.addEventListener('change', validateDates);
        
        // If start date changes, update end date minimum
        startDateInput.addEventListener('change', function() {
            endDateInput.min = startDateInput.value;
            validateDates();
        });
    });
    </script>
    <style>
    .se-meta-section {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 5px;
    }
    </style>
    <?php
}

// Render meta box Pembicara & Moderator
function se_render_speakers_meta_box($post) {
    $speakers = get_post_meta($post->ID, '_se_event_speakers', true);
    if (!is_array($speakers)) $speakers = [];
    ?>
    <div id="se-speakers-wrapper">
        <?php if (!empty($speakers)): ?>
            <?php foreach ($speakers as $i => $speaker):
                $photo_url = !empty($speaker['photo_id']) ? wp_get_attachment_image_url($speaker['photo_id'], 'thumbnail') : '';
            ?>
            <div class="se-speaker-row" style="display:flex; gap:12px; align-items:flex-start; padding:12px; background:#f9f9f9; border-radius:8px; margin-bottom:10px; position:relative;">
                <div style="flex-shrink:0; text-align:center;">
                    <div class="se-speaker-preview" style="width:80px; height:80px; border-radius:50%; overflow:hidden; background:#ddd; margin-bottom:6px; display:flex; align-items:center; justify-content:center;">
                        <?php if ($photo_url): ?>
                            <img src="<?php echo esc_url($photo_url); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span class="dashicons dashicons-camera" style="font-size:30px; color:#999;"></span>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button button-small se-upload-photo">Upload Foto</button>
                    <input type="hidden" name="se_speakers[<?php echo $i; ?>][photo_id]" value="<?php echo esc_attr($speaker['photo_id'] ?? ''); ?>" class="se-photo-id">
                </div>
                <div style="flex:1;">
                    <p style="margin:0 0 6px;"><input type="text" name="se_speakers[<?php echo $i; ?>][name]" value="<?php echo esc_attr($speaker['name'] ?? ''); ?>" placeholder="Nama" style="width:100%;"></p>
                    <p style="margin:0 0 6px;"><input type="text" name="se_speakers[<?php echo $i; ?>][job_title]" value="<?php echo esc_attr($speaker['job_title'] ?? ''); ?>" placeholder="Jabatan, Perusahaan" style="width:100%;"></p>
                    <?php
                        $role = $speaker['role'] ?? 'speaker';
                        $is_custom = !in_array($role, ['speaker', 'moderator']);
                    ?>
                    <p style="margin:0 0 6px;"><select name="se_speakers[<?php echo $i; ?>][role_select]" class="se-role-select" style="width:100%;">
                        <option value="speaker" <?php selected(!$is_custom && $role === 'speaker'); ?>>Speaker</option>
                        <option value="moderator" <?php selected(!$is_custom && $role === 'moderator'); ?>>Moderator</option>
                        <option value="_custom" <?php selected($is_custom); ?>>Lainnya...</option>
                    </select></p>
                    <p style="margin:0; <?php echo $is_custom ? '' : 'display:none;'; ?>" class="se-custom-role-wrap"><input type="text" name="se_speakers[<?php echo $i; ?>][role_custom]" value="<?php echo esc_attr($is_custom ? $role : ''); ?>" placeholder="Ketik role, misal: Panelist, Host, MC" style="width:100%;" class="se-custom-role"></p>
                    <input type="hidden" name="se_speakers[<?php echo $i; ?>][role]" value="<?php echo esc_attr($role); ?>" class="se-role-value">
                </div>
                <button type="button" class="se-remove-speaker" style="position:absolute; top:8px; right:8px; background:#EA242A; color:white; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer; font-size:14px; line-height:24px; text-align:center;" title="Hapus">&times;</button>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" id="se-add-speaker" class="button button-primary" style="margin-top:10px;">+ Tambah Pembicara</button>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let speakerIndex = <?php echo count($speakers); ?>;
        const wrapper = document.getElementById('se-speakers-wrapper');

        // Template row baru
        function createSpeakerRow(idx) {
            const div = document.createElement('div');
            div.className = 'se-speaker-row';
            div.style.cssText = 'display:flex; gap:12px; align-items:flex-start; padding:12px; background:#f9f9f9; border-radius:8px; margin-bottom:10px; position:relative;';
            div.innerHTML = `
                <div style="flex-shrink:0; text-align:center;">
                    <div class="se-speaker-preview" style="width:80px; height:80px; border-radius:50%; overflow:hidden; background:#ddd; margin-bottom:6px; display:flex; align-items:center; justify-content:center;">
                        <span class="dashicons dashicons-camera" style="font-size:30px; color:#999;"></span>
                    </div>
                    <button type="button" class="button button-small se-upload-photo">Upload Foto</button>
                    <input type="hidden" name="se_speakers[${idx}][photo_id]" value="" class="se-photo-id">
                </div>
                <div style="flex:1;">
                    <p style="margin:0 0 6px;"><input type="text" name="se_speakers[${idx}][name]" value="" placeholder="Nama" style="width:100%;"></p>
                    <p style="margin:0 0 6px;"><input type="text" name="se_speakers[${idx}][job_title]" value="" placeholder="Jabatan, Perusahaan" style="width:100%;"></p>
                    <p style="margin:0 0 6px;"><select name="se_speakers[${idx}][role_select]" class="se-role-select" style="width:100%;">
                        <option value="speaker">Speaker</option>
                        <option value="moderator">Moderator</option>
                        <option value="_custom">Lainnya...</option>
                    </select></p>
                    <p style="margin:0; display:none;" class="se-custom-role-wrap"><input type="text" name="se_speakers[${idx}][role_custom]" value="" placeholder="Ketik role, misal: Panelist, Host, MC" style="width:100%;" class="se-custom-role"></p>
                    <input type="hidden" name="se_speakers[${idx}][role]" value="speaker" class="se-role-value">
                </div>
                <button type="button" class="se-remove-speaker" style="position:absolute; top:8px; right:8px; background:#EA242A; color:white; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer; font-size:14px; line-height:24px; text-align:center;" title="Hapus">&times;</button>
            `;
            return div;
        }

        // Tambah speaker
        document.getElementById('se-add-speaker').addEventListener('click', function() {
            wrapper.appendChild(createSpeakerRow(speakerIndex));
            speakerIndex++;
        });

        // Hapus speaker
        wrapper.addEventListener('click', function(e) {
            if (e.target.classList.contains('se-remove-speaker')) {
                e.target.closest('.se-speaker-row').remove();
            }
        });

        // Role select: toggle custom input
        wrapper.addEventListener('change', function(e) {
            if (e.target.classList.contains('se-role-select')) {
                const row = e.target.closest('.se-speaker-row');
                const customWrap = row.querySelector('.se-custom-role-wrap');
                const customInput = row.querySelector('.se-custom-role');
                const hiddenRole = row.querySelector('.se-role-value');
                if (e.target.value === '_custom') {
                    customWrap.style.display = '';
                    customInput.focus();
                    hiddenRole.value = customInput.value || '';
                } else {
                    customWrap.style.display = 'none';
                    customInput.value = '';
                    hiddenRole.value = e.target.value;
                }
            }
        });

        // Sync custom role text to hidden field
        wrapper.addEventListener('input', function(e) {
            if (e.target.classList.contains('se-custom-role')) {
                const row = e.target.closest('.se-speaker-row');
                row.querySelector('.se-role-value').value = e.target.value;
            }
        });

        // Upload foto
        wrapper.addEventListener('click', function(e) {
            if (e.target.classList.contains('se-upload-photo')) {
                e.preventDefault();
                const row = e.target.closest('.se-speaker-row');
                const photoInput = row.querySelector('.se-photo-id');
                const previewDiv = row.querySelector('.se-speaker-preview');

                const frame = wp.media({
                    title: 'Pilih Foto Speaker',
                    button: { text: 'Gunakan Foto' },
                    multiple: false,
                    library: { type: 'image' }
                });

                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    photoInput.value = attachment.id;
                    const url = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    previewDiv.innerHTML = '<img src="' + url + '" style="width:100%; height:100%; object-fit:cover;">';
                });

                frame.open();
            }
        });
    });
    </script>
    <?php
}

// Render meta box Target Audience
function se_render_target_audience_meta_box($post) {
    $audiences = get_post_meta($post->ID, '_se_event_target_audience', true);
    if (!is_array($audiences)) $audiences = [];
    ?>
    <div id="se-audience-wrapper">
        <?php if (!empty($audiences)): ?>
            <?php foreach ($audiences as $i => $audience): ?>
            <div class="se-audience-row" style="display:flex; gap:10px; align-items:center; margin-bottom:8px;">
                <input type="text" name="se_target_audience[]" value="<?php echo esc_attr($audience); ?>" placeholder="Contoh: HR Professional, Business Owner" style="width:100%; padding:8px;">
                <button type="button" class="se-remove-audience" style="background:#EA242A; color:white; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer; font-size:14px; line-height:24px; text-align:center; flex-shrink:0;" title="Hapus">&times;</button>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" id="se-add-audience" class="button button-primary" style="margin-top:10px;">+ Tambah Target Audience</button>
    <p style="margin-top:8px;"><small style="color:#666;">Tambahkan target audience untuk event ini. Contoh: HR Professional, Business Owner, IT Manager, dsb.</small></p>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const audienceWrapper = document.getElementById('se-audience-wrapper');

        function createAudienceRow(value) {
            const div = document.createElement('div');
            div.className = 'se-audience-row';
            div.style.cssText = 'display:flex; gap:10px; align-items:center; margin-bottom:8px;';
            div.innerHTML = `
                <input type="text" name="se_target_audience[]" value="${value || ''}" placeholder="Contoh: HR Professional, Business Owner" style="width:100%; padding:8px;">
                <button type="button" class="se-remove-audience" style="background:#EA242A; color:white; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer; font-size:14px; line-height:24px; text-align:center; flex-shrink:0;" title="Hapus">&times;</button>
            `;
            return div;
        }

        document.getElementById('se-add-audience').addEventListener('click', function() {
            audienceWrapper.appendChild(createAudienceRow(''));
        });

        audienceWrapper.addEventListener('click', function(e) {
            if (e.target.classList.contains('se-remove-audience')) {
                e.target.closest('.se-audience-row').remove();
            }
        });
    });
    </script>
    <?php
}

// Render meta box Konfigurasi Field Formulir
function se_render_form_fields_meta_box($post) {
    $fields = se_get_event_form_fields($post->ID);
    $custom_counter = 0;
    foreach ($fields as $field) {
        if (strpos($field['key'], 'custom_') === 0) {
            $num = intval(str_replace('custom_', '', $field['key']));
            if ($num >= $custom_counter) $custom_counter = $num + 1;
        }
    }
    $locked_keys = ['name', 'email'];
    ?>
    <p style="color:#666; margin-bottom:15px;">Konfigurasi field yang tampil di formulir pendaftaran dan replay. Field <strong>Nama</strong> dan <strong>Email</strong> tidak dapat dihapus.</p>
    <div id="se-form-fields-wrapper">
        <?php foreach ($fields as $i => $field):
            $is_locked = in_array($field['key'], $locked_keys);
        ?>
        <div class="se-field-row" style="display:flex; gap:10px; align-items:center; padding:10px; background:#f9f9f9; border-radius:8px; margin-bottom:8px; flex-wrap:wrap;">
            <input type="hidden" name="se_form_fields[<?php echo $i; ?>][key]" value="<?php echo esc_attr($field['key']); ?>">
            <div style="flex:2; min-width:150px;">
                <label style="font-size:11px; color:#888;">Label</label>
                <input type="text" name="se_form_fields[<?php echo $i; ?>][label]" value="<?php echo esc_attr($field['label']); ?>" style="width:100%; padding:6px;" required>
            </div>
            <div style="flex:1; min-width:100px;">
                <label style="font-size:11px; color:#888;">Tipe</label>
                <select name="se_form_fields[<?php echo $i; ?>][type]" class="se-field-type-select" style="width:100%; padding:6px;" <?php echo $is_locked ? 'disabled' : ''; ?>>
                    <option value="text" <?php selected($field['type'], 'text'); ?>>Text</option>
                    <option value="email" <?php selected($field['type'], 'email'); ?>>Email</option>
                    <option value="tel" <?php selected($field['type'], 'tel'); ?>>Telepon</option>
                    <option value="number" <?php selected($field['type'], 'number'); ?>>Angka</option>
                    <option value="textarea" <?php selected($field['type'], 'textarea'); ?>>Textarea</option>
                    <option value="select" <?php selected($field['type'], 'select'); ?>>Dropdown</option>
                    <option value="checkbox" <?php selected($field['type'], 'checkbox'); ?>>Checkbox</option>
                    <option value="radio" <?php selected($field['type'], 'radio'); ?>>Radio Button</option>
                </select>
                <?php if ($is_locked): ?>
                    <input type="hidden" name="se_form_fields[<?php echo $i; ?>][type]" value="<?php echo esc_attr($field['type']); ?>">
                <?php endif; ?>
            </div>
            <div style="flex:0 0 auto; text-align:center;">
                <label style="font-size:11px; color:#888;">Wajib</label><br>
                <input type="checkbox" name="se_form_fields[<?php echo $i; ?>][required]" value="1" <?php checked(!empty($field['required'])); ?> <?php echo $is_locked ? 'checked disabled' : ''; ?>>
                <?php if ($is_locked): ?>
                    <input type="hidden" name="se_form_fields[<?php echo $i; ?>][required]" value="1">
                <?php endif; ?>
            </div>
            <div style="flex:0 0 auto;">
                <?php if (!$is_locked): ?>
                    <button type="button" class="se-remove-field" style="background:#EA242A; color:white; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer; font-size:14px; line-height:24px; text-align:center;" title="Hapus">&times;</button>
                <?php else: ?>
                    <span style="display:inline-block; width:24px; height:24px;"></span>
                <?php endif; ?>
            </div>
            <div class="se-field-options" style="width:100%; <?php echo !in_array($field['type'] ?? '', ['select', 'checkbox', 'radio']) ? 'display:none;' : ''; ?>">
                <label style="font-size:11px; color:#888;">Opsi (pisahkan dengan koma)</label>
                <input type="text" name="se_form_fields[<?php echo $i; ?>][options]" value="<?php echo esc_attr($field['options'] ?? ''); ?>" style="width:100%; padding:6px;" placeholder="Opsi 1, Opsi 2, Opsi 3">
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" id="se-add-form-field" class="button button-primary" style="margin-top:10px;">+ Tambah Custom Field</button>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let fieldIndex = <?php echo count($fields); ?>;
        let customCounter = <?php echo $custom_counter; ?>;
        const ffWrapper = document.getElementById('se-form-fields-wrapper');

        function createFieldRow(idx, customKey) {
            const div = document.createElement('div');
            div.className = 'se-field-row';
            div.style.cssText = 'display:flex; gap:10px; align-items:center; padding:10px; background:#f9f9f9; border-radius:8px; margin-bottom:8px; flex-wrap:wrap;';
            div.innerHTML = `
                <input type="hidden" name="se_form_fields[${idx}][key]" value="${customKey}">
                <div style="flex:2; min-width:150px;">
                    <label style="font-size:11px; color:#888;">Label</label>
                    <input type="text" name="se_form_fields[${idx}][label]" value="" style="width:100%; padding:6px;" required placeholder="Label field">
                </div>
                <div style="flex:1; min-width:100px;">
                    <label style="font-size:11px; color:#888;">Tipe</label>
                    <select name="se_form_fields[${idx}][type]" class="se-field-type-select" style="width:100%; padding:6px;">
                        <option value="text">Text</option>
                        <option value="email">Email</option>
                        <option value="tel">Telepon</option>
                        <option value="number">Angka</option>
                        <option value="textarea">Textarea</option>
                        <option value="select">Dropdown</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="radio">Radio Button</option>
                    </select>
                </div>
                <div style="flex:0 0 auto; text-align:center;">
                    <label style="font-size:11px; color:#888;">Wajib</label><br>
                    <input type="checkbox" name="se_form_fields[${idx}][required]" value="1">
                </div>
                <div style="flex:0 0 auto;">
                    <button type="button" class="se-remove-field" style="background:#EA242A; color:white; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer; font-size:14px; line-height:24px; text-align:center;" title="Hapus">&times;</button>
                </div>
                <div class="se-field-options" style="width:100%; display:none;">
                    <label style="font-size:11px; color:#888;">Opsi (pisahkan dengan koma)</label>
                    <input type="text" name="se_form_fields[${idx}][options]" value="" style="width:100%; padding:6px;" placeholder="Opsi 1, Opsi 2, Opsi 3">
                </div>
            `;
            return div;
        }

        document.getElementById('se-add-form-field').addEventListener('click', function() {
            ffWrapper.appendChild(createFieldRow(fieldIndex, 'custom_' + customCounter));
            fieldIndex++;
            customCounter++;
        });

        ffWrapper.addEventListener('click', function(e) {
            if (e.target.classList.contains('se-remove-field')) {
                e.target.closest('.se-field-row').remove();
            }
        });

        ffWrapper.addEventListener('change', function(e) {
            if (e.target.classList.contains('se-field-type-select')) {
                const row = e.target.closest('.se-field-row');
                const optionsDiv = row.querySelector('.se-field-options');
                optionsDiv.style.display = ['select','checkbox','radio'].includes(e.target.value) ? '' : 'none';
            }
        });
    });
    </script>
    <?php
}

// Simpan metadata saat event disimpan
function se_save_event_meta($post_id) {
    // Cek apakah ini autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    // Cek nonce untuk keamanan
    if (!isset($_POST['se_event_meta_nonce']) || !wp_verify_nonce($_POST['se_event_meta_nonce'], 'se_event_meta_nonce')) {
        return;
    }

    // Cek jenis post
    if (get_post_type($post_id) !== 'event') return;
    
    // Cek jika user memiliki izin
    if (!current_user_can('edit_post', $post_id)) return;

    // Validasi tanggal (server-side)
    if (isset($_POST['se_event_start_date']) && isset($_POST['se_event_end_date']) && 
        isset($_POST['se_event_start_time']) && isset($_POST['se_event_end_time'])) {
        
        $start_datetime = strtotime($_POST['se_event_start_date'] . ' ' . $_POST['se_event_start_time']);
        $end_datetime = strtotime($_POST['se_event_end_date'] . ' ' . $_POST['se_event_end_time']);
        
        if ($end_datetime < $start_datetime) {
            // Add error message
            add_settings_error(
                'se_event_dates',
                'se_invalid_dates',
                'Error: Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
                'error'
            );
            
            // Show error message
            settings_errors('se_event_dates');
            return;
        }
    }

    // Simpan masing-masing meta jika tersedia
    if (isset($_POST['se_event_start_date'])) {
        update_post_meta($post_id, '_se_event_start_date', sanitize_text_field($_POST['se_event_start_date']));
    }
    
    if (isset($_POST['se_event_start_time'])) {
        update_post_meta($post_id, '_se_event_start_time', sanitize_text_field($_POST['se_event_start_time']));
    }

    if (isset($_POST['se_event_end_date'])) {
        update_post_meta($post_id, '_se_event_end_date', sanitize_text_field($_POST['se_event_end_date']));
    }
    
    if (isset($_POST['se_event_end_time'])) {
        update_post_meta($post_id, '_se_event_end_time', sanitize_text_field($_POST['se_event_end_time']));
    }

    if (isset($_POST['se_event_location'])) {
        update_post_meta($post_id, '_se_event_location', sanitize_text_field($_POST['se_event_location']));
    }

    if (isset($_POST['se_event_quota'])) {
        update_post_meta($post_id, '_se_event_quota', intval($_POST['se_event_quota']));
    }

    if (isset($_POST['se_event_replay_url'])) {
        update_post_meta($post_id, '_se_event_replay_url', esc_url_raw($_POST['se_event_replay_url']));
    }

    if (isset($_POST['se_event_google_form_url'])) {
        update_post_meta($post_id, '_se_event_google_form_url', esc_url_raw($_POST['se_event_google_form_url']));
    }

    if (isset($_POST['se_event_form_title'])) {
        update_post_meta($post_id, '_se_event_form_title', sanitize_text_field($_POST['se_event_form_title']));
    }

    if (isset($_POST['se_event_form_subtitle'])) {
        update_post_meta($post_id, '_se_event_form_subtitle', sanitize_textarea_field($_POST['se_event_form_subtitle']));
    }

    // Simpan speakers
    if (isset($_POST['se_speakers']) && is_array($_POST['se_speakers'])) {
        $speakers = [];
        foreach ($_POST['se_speakers'] as $speaker) {
            $name = sanitize_text_field($speaker['name'] ?? '');
            if (empty($name)) continue; // Skip empty entries
            $role = sanitize_text_field($speaker['role'] ?? 'speaker');
            if (empty($role)) $role = 'speaker';
            $speakers[] = [
                'photo_id'  => intval($speaker['photo_id'] ?? 0),
                'name'      => $name,
                'job_title' => sanitize_text_field($speaker['job_title'] ?? ''),
                'role'      => $role,
            ];
        }
        update_post_meta($post_id, '_se_event_speakers', $speakers);
    } else {
        update_post_meta($post_id, '_se_event_speakers', []);
    }

    // Simpan target audience
    if (isset($_POST['se_target_audience']) && is_array($_POST['se_target_audience'])) {
        $audiences = [];
        foreach ($_POST['se_target_audience'] as $audience) {
            $clean = sanitize_text_field($audience);
            if (!empty($clean)) $audiences[] = $clean;
        }
        update_post_meta($post_id, '_se_event_target_audience', $audiences);
    } else {
        update_post_meta($post_id, '_se_event_target_audience', []);
    }

    // Simpan form fields config
    if (isset($_POST['se_form_fields']) && is_array($_POST['se_form_fields'])) {
        $form_fields = [];
        foreach ($_POST['se_form_fields'] as $field) {
            $key = sanitize_key($field['key'] ?? '');
            $label = sanitize_text_field($field['label'] ?? '');
            if (empty($key) || empty($label)) continue;

            $field_data = [
                'key'      => $key,
                'label'    => $label,
                'type'     => sanitize_text_field($field['type'] ?? 'text'),
                'required' => !empty($field['required']),
            ];

            if (in_array($field_data['type'], ['select', 'checkbox', 'radio']) && !empty($field['options'])) {
                $field_data['options'] = sanitize_text_field($field['options']);
            }

            $form_fields[] = $field_data;
        }

        // Enforce name and email always present and required
        $has_name = false;
        $has_email = false;
        foreach ($form_fields as &$ff) {
            if ($ff['key'] === 'name') { $ff['required'] = true; $has_name = true; }
            if ($ff['key'] === 'email') { $ff['required'] = true; $has_email = true; }
        }
        unset($ff);

        if (!$has_name) {
            array_unshift($form_fields, ['key' => 'name', 'label' => 'Nama', 'type' => 'text', 'required' => true]);
        }
        if (!$has_email) {
            array_splice($form_fields, 1, 0, [['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true]]);
        }

        update_post_meta($post_id, '_se_event_form_fields', $form_fields);
    }
}
add_action('save_post', 'se_save_event_meta');

// Helper function to combine date and time for display
function se_get_event_datetime($post_id, $type = 'start') {
    $date = get_post_meta($post_id, "_se_event_{$type}_date", true);
    $time = get_post_meta($post_id, "_se_event_{$type}_time", true);
    
    if (empty($date)) return '';
    
    if (!empty($time)) {
        return $date . ' ' . $time;
    }
    
    return $date;
}
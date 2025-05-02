<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$wpaicg_cron_added = get_option('wpaicg_cron_builder_added','');
$humanReadableBuilder = (!empty($wpaicg_cron_added)) ? date_i18n('y-m-d H:i', $wpaicg_cron_added) : 'NA';
$wpaicg_builder_types = get_option('wpaicg_builder_types',[]);
$schedule_options = [
    'none' => 'None',
    '5minutes' => 'Every 5 Minutes',
    '15minutes' => 'Every 15 Minutes',
    '30minutes' => 'Every 30 Minutes',
    '1hour' => 'Every 1 Hour',
    '2hours' => 'Every 2 Hours',
    '6hours' => 'Every 6 Hours',
    '12hours' => 'Every 12 Hours',
    '1day' => 'Every Day',
    '1week' => 'Every Week'
];

$schedule_builder = get_option('wpaicg_cron_builder_schedule', 'none');
?>
<table class="wp-list-table widefat fixed striped table-view-list comments">
<thead>
        <tr>
            <th style="width: 60px;"><?php echo esc_html__('#', 'gpt3-ai-content-generator'); ?></th>
            <th style="width: 60px;"><?php echo esc_html__('Status', 'gpt3-ai-content-generator'); ?></th>
            <th style="width: 120px;"><?php echo esc_html__('Last Run', 'gpt3-ai-content-generator'); ?></th>
            <th style="width: 60px;"><?php echo esc_html__('Manual', 'gpt3-ai-content-generator'); ?></th>
            <th><?php echo esc_html__('Schedule', 'gpt3-ai-content-generator'); ?></th>
            <th><?php echo esc_html__('Cron', 'gpt3-ai-content-generator'); ?></th>
        </tr>
        </thead>
    <tbody>
        <tr>
            <td>Scan</td>
            <td style="color: <?php echo empty($wpaicg_cron_added) ? '#ff0000' : '#008000'; ?>;"><?php echo empty($wpaicg_cron_added) ? 'OFF' : 'ON'; ?></td>
            <td><?php echo esc_html($humanReadableBuilder); ?></td>
            <td>
                <button id="triggerAutoScan" class="button button-primary" title="Trigger Queue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-play"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                </button>
            </td>
            <td>
                <select id="schedule_builder" data-task="builder" style="width: 120px;">
                    <?php foreach ($schedule_options as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($schedule_builder, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><a href="#" class="view-instructions" data-instruction="builder">Instructions</a></td>
        </tr>
    </tbody>
</table>
<p></p>
<h1>Scanned Content</h1>
<?php
if ($wpaicg_builder_types && is_array($wpaicg_builder_types) && count($wpaicg_builder_types)) {
    foreach ($wpaicg_builder_types as $wpaicg_builder_type) {
        // Count total data
        $total_data = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(p.ID) FROM {$wpdb->posts} p WHERE p.post_type = %s AND p.post_status = %s",
                $wpaicg_builder_type,
                'publish' // Use placeholder for status as well
            )
        );

        // Count for each status
        $error_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(p.ID)
                 FROM {$wpdb->postmeta} m
                 LEFT JOIN {$wpdb->posts} p ON p.ID = m.post_id
                 WHERE p.post_type = %s
                 AND p.post_status = %s
                 AND m.meta_key = %s
                 AND m.meta_value = %s",
                 $wpaicg_builder_type,
                 'publish',        // Placeholder for status
                 'wpaicg_indexed', // Placeholder for meta_key
                 'error'           // Placeholder for meta_value
             )
        );

        $skip_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(p.ID)
                 FROM {$wpdb->postmeta} m
                 LEFT JOIN {$wpdb->posts} p ON p.ID = m.post_id
                 WHERE p.post_type = %s
                 AND p.post_status = %s
                 AND m.meta_key = %s
                 AND m.meta_value = %s",
                 $wpaicg_builder_type,
                 'publish',        // Placeholder for status
                 'wpaicg_indexed', // Placeholder for meta_key
                 'skip'            // Placeholder for meta_value
             )
        );

        // After calculating $error_count and before the if statement
        $error_posts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID, p.post_title
                 FROM {$wpdb->postmeta} m
                 LEFT JOIN {$wpdb->posts} p ON p.ID = m.post_id
                 WHERE p.post_type = %s
                 AND p.post_status = %s
                 AND m.meta_key = %s
                 AND m.meta_value = %s",
                 $wpaicg_builder_type,
                 'publish',        // Placeholder for status
                 'wpaicg_indexed', // Placeholder for meta_key
                 'error'           // Placeholder for meta_value
            )
        );

        // Similar SQL query and fetch for $skip_posts
        $skip_posts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID, p.post_title
                 FROM {$wpdb->postmeta} m
                 LEFT JOIN {$wpdb->posts} p ON p.ID = m.post_id
                 WHERE p.post_type = %s
                 AND p.post_status = %s
                 AND m.meta_key = %s
                 AND m.meta_value = %s",
                 $wpaicg_builder_type,
                 'publish',        // Placeholder for status
                 'wpaicg_indexed', // Placeholder for meta_key
                 'skip'           // Placeholder for meta_value
            )
        );

        $completed_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(p.ID)
                 FROM {$wpdb->postmeta} m
                 LEFT JOIN {$wpdb->posts} p ON p.ID = m.post_id
                 WHERE p.post_type = %s
                 AND p.post_status = %s
                 AND m.meta_key = %s
                 AND m.meta_value = %s",
                 $wpaicg_builder_type,
                 'publish',         // Placeholder for status
                 'wpaicg_indexed',  // Placeholder for meta_key
                 'yes'              // Placeholder for meta_value
             )
        );

        if ($total_data > 0) {
            // Use completed_count for the percentage calculation
            $percent_process = ceil($completed_count * 100 / $total_data);
            ?>
            <div class="wpaicg-builder-process wpaicg-builder-process-<?php echo esc_html($wpaicg_builder_type)?>">
                <div class="nice-form-group">
                <span class="wpaicg-index-results">
                    <?php
                    // Display the type of content
                    if ($wpaicg_builder_type == 'post') {
                        echo esc_html__('Posts', 'gpt3-ai-content-generator');
                    } elseif ($wpaicg_builder_type == 'page') {
                        echo esc_html__('Pages', 'gpt3-ai-content-generator');
                    } elseif ($wpaicg_builder_type == 'product') {
                        echo esc_html__('Products', 'gpt3-ai-content-generator');
                    } else {
                        echo esc_html(ucwords(str_replace(array('-', '_'), ' ', $wpaicg_builder_type)));
                    }
                    ?>
                    <!-- Show completed_count against total_data for accuracy -->
                    (<?php echo esc_html($completed_count)?>/<?php echo esc_html($total_data)?>)
                        <!-- Conditional display for error, skip, completed -->
                        <?php 
                            if ($error_count > 0) {
                                // Escape builder type for JS context and error count for HTML content
                                echo sprintf(
                                    '<small><a href="javascript:void(0);" onclick="toggleVisibility(\'error-%s\');">%s</a>%s</small>',
                                    esc_js($wpaicg_builder_type), // Escape for JS context within onclick
                                    esc_html__('Error:', 'gpt3-ai-content-generator'), // Use translation function for "Error:"
                                    esc_html($error_count)        // Escape count for HTML content
                                );

                                // Fetch and display post titles for errors with clickable edit links and a Re-Index button
                                foreach ($error_posts as $post) {
                                    $edit_link = get_edit_post_link($post->ID);
                                    // Escape builder type for class attribute, edit link for href, ID for data attribute, title for HTML content
                                    echo sprintf(
                                        '<div class="error-%s" style="display:none;"><small style="margin-bottom: 1em;"><a href="%s" target="_blank">%s</a> <button style="padding: 2px;font-size: xx-small;margin-top: -0.5em;" data-id="%s" class="button button-primary button-small wpaicg_reindex">%s</button></small></div>',
                                        esc_attr($wpaicg_builder_type), // Escape for class attribute
                                        esc_url($edit_link),            // Escape URL for href attribute
                                        esc_html($post->post_title),    // Escape title for HTML content
                                        esc_attr($post->ID),            // Escape ID for data attribute
                                        esc_html__('Retry', 'gpt3-ai-content-generator') // Use translation function for "Retry"
                                    );
                                }
                            }

                            if ($skip_count > 0) {
                                // Escape builder type for JS context and skip count for HTML content
                                echo sprintf(
                                    '<small style="padding-top: 1em;"><a href="javascript:void(0);" onclick="toggleVisibility(\'skip-%s\');">%s</a>%s</small>',
                                    esc_js($wpaicg_builder_type), // Escape for JS context within onclick
                                    esc_html__('Skipped:', 'gpt3-ai-content-generator'), // Use translation function for "Skipped:"
                                    esc_html($skip_count)         // Escape count for HTML content
                                );

                                // Fetch and display post titles for skips with clickable edit links and a Re-Index button
                                foreach ($skip_posts as $post) {
                                    $edit_link = get_edit_post_link($post->ID);
                                    // Escape builder type for class attribute, edit link for href, ID for data attribute, title for HTML content
                                    echo sprintf(
                                        '<div class="skip-%s" style="display:none;"><small style="margin-bottom: 1em;"><a href="%s" target="_blank">%s</a> <button style="padding: 2px;font-size: xx-small;margin-top: -0.5em;" data-id="%s" class="button button-primary button-small wpaicg_reindex">%s</button></small></div>',
                                        esc_attr($wpaicg_builder_type), // Escape for class attribute
                                        esc_url($edit_link),            // Escape URL for href attribute
                                        esc_html($post->post_title),    // Escape title for HTML content
                                        esc_attr($post->ID),            // Escape ID for data attribute
                                        esc_html__('Retry', 'gpt3-ai-content-generator') // Use translation function for "Retry"
                                    );
                                }
                            }
                        ?>
                                        <?php
                    if ($completed_count > 0) {
                        // Use sprintf for translation and escaping the count
                        echo sprintf(
                            /* translators: %d: Number of completed items */
                            esc_html__('Completed: %d', 'gpt3-ai-content-generator'),
                            (int) $completed_count // Cast to int for %d and general safety
                        );
                    }
                    ?>
                    </span>
                    <div class="wpaicg-builder-process-content">
                        <span class="wpaicg-percent" style="width: <?php echo esc_html($percent_process)?>%"></span>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}
?>

<script>
    jQuery(document).ready(function ($){
        function wpaicgLoading(btn){
            btn.attr('disabled','disabled');
            if(!btn.find('spinner').length){
                btn.append('<span class="spinner"></span>');
            }
            btn.find('.spinner').css('visibility','unset');
        }
        function wpaicgRmLoading(btn){
            btn.removeAttr('disabled');
            btn.find('.spinner').remove();
        }

        $(document).on('click','.wpaicg_reindex' ,function (e){
            var btn = $(e.currentTarget);
            var id = btn.attr('data-id');
            var conf = confirm('<?php echo esc_html__('Are you sure?','gpt3-ai-content-generator')?>');
            if(conf){
                $.ajax({
                    url: '<?php echo esc_js(admin_url('admin-ajax.php')); // Escape for JS context ?>',
                    data: {action: 'wpaicg_builder_reindex', id: id,'nonce': '<?php echo esc_js(wp_create_nonce('wpaicg-ajax-nonce')); // Escape for JS context ?>'},
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function (){
                        wpaicgLoading(btn);
                    },
                    success: function (res){
                        wpaicgRmLoading(btn);
                        if(res.status === 'success'){
                            $('#wpaicg-builder-'+id+' .builder-status').html('<span style="color: #d73e1c;font-weight: bold;"><?php echo esc_html__('Pending','gpt3-ai-content-generator')?></span>');
                            btn.remove();
                        }
                        else{
                            alert(res.msg);
                        }
                    },
                    error: function (){
                        wpaicgRmLoading(btn);
                        alert('<?php echo esc_html__('Something went wrong','gpt3-ai-content-generator')?>');
                    }
                })
            }
        });

        function triggerAutoScan(task) {
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "trigger_wpaicg_cron",
                    task: task
                },
                success: function(response) {
                    alert(response.data);
                },
                error: function() {
                    alert("Failed to trigger the task.");
                }
            });
        }

        $('#triggerAutoScan').click(function() { triggerAutoScan("wpaicg_builder=yes"); });

        // Function to save schedule
        function saveSchedule(task, value) {
            $.ajax({
                url: '<?php echo esc_js(admin_url('admin-ajax.php')); // Escape for JS context ?>',
                type: 'post',
                data: {
                    action: 'save_schedule',
                    task: task,
                    value: value,
                    nonce: '<?php echo esc_js(wp_create_nonce('save_schedule_nonce')); ?>'
                },
                success: function(response) {
                    if (!response.success) {
                        alert('Failed to save schedule.');
                    }
                }
            });
        }

        // Handle schedule change
        $('#schedule_builder').on('change', function() {
            var task = $(this).data('task');
            var value = $(this).val();
            saveSchedule(task, value);
        });

    })
</script>
<script>
function toggleVisibility(className) {
    var elements = document.getElementsByClassName(className);
    for (var i = 0; i < elements.length; i++) {
        elements[i].style.display = elements[i].style.display === 'none' ? '' : 'none';
    }
}
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewInstructionsLinks = document.querySelectorAll('.view-instructions');

        viewInstructionsLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Check if the next sibling is an instruction row and toggle visibility
                const nextSibling = this.closest('tr').nextElementSibling;
                if (nextSibling && nextSibling.classList.contains('instruction-row')) {
                    nextSibling.remove(); // Remove the instruction row if it already exists
                    return; // Exit the function to not add it again
                }

                // Remove any existing instruction row from other "View Instructions" clicks
                document.querySelectorAll('.instruction-row').forEach(row => row.remove());

                // Identify which instruction to display
                const instructionType = this.dataset.instruction;
                let cronCommand = '';
                let instructionText = 'Use this command to set up your cron job on the server. Read the guide <a href="https://docs.aipower.org/docs/AutoGPT/gpt-agents#cron-job-setup" target="_blank">here</a>.';
                switch (instructionType) {
                    case 'builder':
                        cronCommand = '* * * * * php <?php echo esc_html(ABSPATH) ?>index.php -- wpaicg_builder=yes';
                        break;
                }

                // Create and insert the instruction row below the current row
                const instructionRow = document.createElement('tr');
                instructionRow.className = 'instruction-row';
                instructionRow.innerHTML = `<td colspan="6"><div class="wpaicg-code-container">${instructionText}<br><code>${cronCommand}</code></div></td>`;
                this.closest('tr').after(instructionRow);
            });
        });
    });
</script>
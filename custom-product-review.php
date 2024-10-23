<?php 

function process_custom_review() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('You are not allowed to perform this operation.');
    }

    if (isset($_POST['product_id'], $_POST['review_author'], $_POST['review_content'], $_POST['review_rating'])) {
        $product_id = ( isset( $_POST['product_id'] ) )  ? sanitize_text_field( intval($_POST['product_id']) )  : '';
        $review_author_email = ( isset( $_POST['review_author_email'] ) ) ? sanitize_text_field( $_POST['review_author_email'] )   : '';
        $review_author = sanitize_text_field($_POST['review_author']);
        $review_content = ( isset( $_POST['review_content'] ) )  ? sanitize_textarea_field($_POST['review_content'])  : '';
        $review_rating = ( isset( $_POST['review_rating'] ) ) ? absint( $_POST['review_rating'] ) : 5;
        
        $review_verified   = ( isset( $_POST['review_verified'] ) ) ? absint( $_POST['review_verified'] ) : '';
        
        $review_date  = ( isset( $_POST['review_date'] ) )  ? sanitize_text_field( $_POST['review_date'] )  : current_time( 'mysql' );
        
        if ( empty($product_id) || empty($review_author) || empty($review_content) ) {
           wp_redirect(add_query_arg(array('page' => 'add_custom_review', 'error' => 'true'), admin_url('edit.php?post_type=product')));
           exit;
       } 

       $commentdata = array(
        'comment_post_ID' => $product_id,
        'comment_author' => $review_author,
        'comment_author_email' => $review_author_email,
        'comment_content' => $review_content,
        'comment_type' => 'review',
        'comment_parent'  => 0,
        'comment_approved' => 0,
        'comment_date'    => $review_date,
        'comment_date_gmt' => get_gmt_from_date( $review_date ),
    );

       $comment_id = wp_insert_comment($commentdata);
       
       if ($comment_id) {
          add_comment_meta( $comment_id, 'rating', (int) esc_attr( $review_rating ), true );
		   // Add verified meta if necessary
          if (!empty($review_verified)) {
            add_comment_meta($comment_id, 'verified', (int) esc_attr($review_verified), true);
        }
        
		  // Update the comment approval status to approved
        wp_update_comment(array(
            'comment_ID' => $comment_id,
            'comment_approved' => 1,
        ));
        wp_redirect(add_query_arg(array('page' => 'add_custom_review', 'success' => 'true'), admin_url('edit.php?post_type=product')));
        exit;
    } else {
        wp_redirect(add_query_arg(array('page' => 'add_custom_review', 'error' => 'true'), admin_url('edit.php?post_type=product')));
        exit;
    }

}
}
add_action('admin_post_process_custom_review', 'process_custom_review');


function add_custom_review_button() {
    global $pagenow;

    if ('edit-comments.php' == $pagenow && isset($_GET['comment_type']) && 'review' == $_GET['comment_type']) {
        echo '<a href="#" id="add-custom-review" class="page-title-action">Add Custom Review</a>';
        ?>
        <script type="text/javascript">
            document.getElementById('add-custom-review').addEventListener('click', function() {
                window.location.href = '<?php echo admin_url('edit.php?post_type=product&page=add_custom_review'); ?>';
            });
        </script>
        <?php
    }
}
add_action('admin_notices', 'add_custom_review_button');


function register_custom_review_submenu() {
    add_submenu_page(
        'edit.php?post_type=product',
        'Add Custom Review',          // Page title
        'Add Custom Review',          // Menu title
        'manage_woocommerce',             // Capability
        'add_custom_review',          // Menu slug
        'custom_review_page_html'     // Callback function
    );
}
add_action('admin_menu', 'register_custom_review_submenu');


function custom_review_page_html() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Check for messages
    $message = '';
    if (isset($_GET['success'])) {
        $message = '<div id="message" class="updated notice is-dismissible"><p>Review successfully added.</p></div>';
    } elseif (isset($_GET['error'])) {
        $message = '<div id="message" class="error notice is-dismissible"><p>Failed to add review.</p></div>';
    }

    echo $message;

    ?>
    <div class="wrap">
        <h1>Add a Custom Review</h1>
        <form id="custom-review-form" action="<?php echo admin_url('admin-post.php'); ?>" method="post">
            <input type="hidden" name="action" value="process_custom_review">
            <?php wp_nonce_field('custom_review_nonce_action', 'custom_review_nonce'); ?>
            
            <div class="form-group">
                <label for="product_name">Product Name</label>
                <input type="text" id="product_name" name="product_name" class="form-control" placeholder="Start typing product name" required>
                <input type="hidden" id="product_id" name="product_id">
            </div>
            <div class="form-group">
                <label for="review_author">Reviewer Name</label>
                <input type="text" id="review_author" name="review_author" class="form-control" placeholder="Enter Reviewer Name" required>
            </div>
            
            <div class="form-group">
                <label for="review_author_email">Reviewer Email (Optional)</label>
                <input type="email" id="review_author_email" name="review_author_email" class="form-control" placeholder="Enter Reviewer Email if available">
            </div>
            
            <div class="form-group">
                <label for="review_content">Review Content</label>
                <textarea id="review_content" name="review_content" class="form-control" placeholder="Enter Review Content" required></textarea>
                
            </div>
            <div class="form-group">
                <label for="review_rating">Rating (1-5)</label>
                <select class="form-control" id="review_rating" name="review_rating" required>
                  <option value="1"><?php esc_html_e('1 Star'); ?></option>
                  <option value="2"><?php esc_html_e('2 Stars'); ?></option>
                  <option value="3"><?php esc_html_e('3 Stars'); ?></option>
                  <option value="4"><?php esc_html_e('4 Stars'); ?></option>
                  <option value="5" selected><?php esc_html_e('5 Stars'); ?></option>
              </select>
              
          </div>
          <div class="form-group">
            <label for="review_verified">Verified?</label>
            <input type="checkbox" class="checkbox" value="1" name="review_verified" id="review_verified" checked>
            <span class="description"><?php esc_html_e('This will mark this review as verfied (left by a verified owner).'); ?></span>
        </div>
        <div class="form-group">
         
         <label for="review_date"><?php esc_html_e('Date'); ?></label>
         <?php $date = new DateTime(); ?>
         <input type="datetime-local" value="<?php echo $date->format('Y-m-d\TH:i'); ?>" name="review_date" id="review_date">
     </div>

     <?php submit_button('Submit Review', 'primary', 'submit_review'); ?>
 </form>
</div>
<style>
    #custom-review-form {
        max-width: 600px;
        margin: 0 auto;
    }
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 5px;
    }
    input[type="text"],
    input[type="email"],
    input[type="number"],
    textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }
    textarea {
        height: 150px;
        resize: vertical;
    }
    .button-primary {
        background-color: #007cba;
        border-color: #007cba;
        box-shadow: none;
        text-shadow: none;
    }
    .button-primary:hover {
        background-color: #005a7e;
        border-color: #005a7e;
    }
</style>
<script>
  jQuery(document).ready(function($) {
    $( "#product_name" ).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                dataType: 'json',
                data: {
                    action: 'woocommerce_product_autocomplete',
                    term: request.term,
                    security: '<?php echo wp_create_nonce("search_products_nonce"); ?>'
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 3,
        select: function(event, ui) {
            $('#product_name').val(ui.item.label);
            $('#product_id').val(ui.item.id);
            return false;
        }
    });
});

</script>
<?php
}

function enqueue_admin_scripts_and_styles() {
    // Ensure jQuery and jQuery UI are loaded
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-autocomplete');

    // Load jQuery UI CSS from a CDN for the sake of example (Consider hosting your own in production)
    wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts_and_styles');


function woocommerce_ajax_product_search() {
    if (isset($_GET['term'])) {
        // Optional security check, make sure you pass 'security' parameter in AJAX call
        check_ajax_referer('search_products_nonce', 'security');

        $term = wc_clean(wp_unslash($_GET['term']));
        $data_store = WC_Data_Store::load('product');
        $ids = $data_store->search_products($term, '', false);

        $results = array();
        foreach ($ids as $id) {
            $product = wc_get_product($id);
            if (is_object($product)) {
                $results[] = array('id' => $id, 'label' => $product->get_name());
            }
        }

        wp_send_json($results);
    }
    wp_die();
}
add_action('wp_ajax_woocommerce_product_autocomplete', 'woocommerce_ajax_product_search');

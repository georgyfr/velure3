<?php
/**
 * Velure Core — Testimonial CPT
 *
 * @package VelureCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'velure_core_register_testimonial_cpt' );
function velure_core_register_testimonial_cpt() {
	register_post_type( 'velure_testimonial', array(
		'labels' => array(
			'name'          => __( 'Temoignages', 'velure-core' ),
			'singular_name' => __( 'Temoignage', 'velure-core' ),
			'menu_name'     => __( 'Temoignages', 'velure-core' ),
			'add_new'       => __( 'Ajouter un temoignage', 'velure-core' ),
			'add_new_item'  => __( 'Ajouter un temoignage', 'velure-core' ),
			'edit_item'     => __( 'Modifier le temoignage', 'velure-core' ),
			'all_items'     => __( 'Tous les temoignages', 'velure-core' ),
		),
		'public'         => false,
		'show_ui'        => true,
		'show_in_menu'   => true,
		'supports'       => array( 'title', 'editor', 'thumbnail' ),
		'menu_icon'      => 'dashicons-format-quote',
		'capability_type' => 'post',
	) );
}

/* ── Meta Box ── */
add_action( 'add_meta_boxes', 'velure_core_testimonial_meta_boxes' );
function velure_core_testimonial_meta_boxes() {
	add_meta_box( 'velure_testimonial_details', __( 'Details du temoignage', 'velure-core' ), 'velure_core_testimonial_meta_box_callback', 'velure_testimonial', 'normal', 'high' );
}

function velure_core_testimonial_meta_box_callback( $post ) {
	wp_nonce_field( 'velure_core_testimonial_nonce', 'velure_core_testimonial_nonce_field' );
	$stars = get_post_meta( $post->ID, '_velure_stars', true ) ?: 5;
	$role  = get_post_meta( $post->ID, '_velure_role', true );
	?>
	<p><label><strong><?php esc_html_e( 'Note :', 'velure-core' ); ?></strong></label><br/>
	<select name="_velure_stars" style="width:100%;max-width:120px;margin-top:4px;">
		<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
			<option value="<?php echo $i; ?>" <?php selected( $stars, $i ); ?>><?php echo $i; ?> <?php echo $i > 1 ? esc_html__( 'etoiles', 'velure-core' ) : esc_html__( 'etoile', 'velure-core' ); ?></option>
		<?php endfor; ?>
	</select></p>
	<p><label><strong><?php esc_html_e( 'Role / Statut :', 'velure-core' ); ?></strong></label><br/>
	<input type="text" name="_velure_role" value="<?php echo esc_attr( $role ); ?>" style="width:100%;margin-top:4px;" placeholder="<?php esc_attr_e( 'Ex : Cliente fidele depuis 2023', 'velure-core' ); ?>" /></p>
	<?php
}

add_action( 'save_post_velure_testimonial', 'velure_core_save_testimonial_meta', 10, 2 );
function velure_core_save_testimonial_meta( $post_id, $post ) {
	if ( ! isset( $_POST['velure_core_testimonial_nonce_field'] ) || ! wp_verify_nonce( $_POST['velure_core_testimonial_nonce_field'], 'velure_core_testimonial_nonce' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	foreach ( array( '_velure_stars', '_velure_role' ) as $field ) {
		if ( isset( $_POST[ $field ] ) ) update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
	}
}
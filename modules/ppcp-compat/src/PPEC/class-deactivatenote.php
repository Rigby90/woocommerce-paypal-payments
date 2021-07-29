<?php
/**
 * Deactivate PayPal Express Checkout inbox note.
 *
 * @package WooCommerce\PayPalCommerce\Compat\PPEC
 */

declare(strict_types=1);

namespace WooCommerce\PayPalCommerce\Compat\PPEC;

use Automattic\WooCommerce\Admin\Notes\Note;
use Automattic\WooCommerce\Admin\Notes\Notes;
use Automattic\WooCommerce\Admin\Notes\NoteTraits;

class DeactivateNote {

	use NoteTraits;

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'ppcp-disable-ppxo-note';

	/**
	 * Undocumented.
	 *
	 * @param boolean $use_subscriptions_compat_layer
	 * @return void
	 */
	public static function init() {
		if ( ! PPECHelper::is_plugin_active() ) {
			self::maybe_mark_note_as_actioned();
			return;
		}

		self::possibly_add_note();
	}

	/**
	 * Get the note.
	 *
	 * @return Automatic\WooCommerce\Admin\Notes\Note
	 */
	public static function get_note() {
		if ( PPECHelper::site_has_ppec_subscriptions() ) {
			$msg = __(
				'Placeholder text (Subscriptions).',
				'woocommerce-paypal-payments'
			);
		} else {
			$msg = __(
				'Placeholder text.',
				'woocommerce-paypal-payments'
			);
		}

		$note = new Note();
		$note->set_name( self::NOTE_NAME );
		$note->set_type( Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
		$note->set_source( 'woocommerce-paypal-payments' );
		$note->set_title(
			__( 'Action Required: Deactivate PayPal Checkout', 'woocommerce-paypal-payments' )
		);
		$note->set_content( $msg );
		$note->add_action(
			'deactivate-paypal-checkout-plugin',
			__( 'Deactivate PayPal Checkout', 'woocommerce-paypal-payments' ),
			admin_url( 'plugins.php?action=deactivate&plugin=' . rawurlencode( PPECHelper::PPEC_PLUGIN_FILE ) . '&plugin_status=all&paged=1&_wpnonce=' . wp_create_nonce( 'deactivate-plugin_' . PPECHelper::PPEC_PLUGIN_FILE ) ),
			Note::E_WC_ADMIN_NOTE_UNACTIONED,
			true
		);
		$note->add_action(
			'learn-more',
			__( 'Learn More', 'woocommerce-paypal-payments' ),
			'https://docs.woocommerce.com/document/woocommerce-paypal-payments/paypal-payments-upgrade-guide/',
			Note::E_WC_ADMIN_NOTE_UNACTIONED
		);

		return $note;
	}

	private static function maybe_mark_note_as_actioned() {
		$data_store = Notes::load_data_store();
		$note_ids   = $data_store->get_notes_with_name( self::NOTE_NAME );

		if ( empty( $note_ids ) ) {
			return;
		}

		$note = Notes::get_note( $note_ids[0] );

		if ( Note::E_WC_ADMIN_NOTE_ACTIONED !== $note->get_status() ) {
			$note->set_status( Note::E_WC_ADMIN_NOTE_ACTIONED );
			$note->save();
		}
	}

}

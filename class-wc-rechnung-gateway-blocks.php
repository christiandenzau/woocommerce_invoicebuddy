<?php

if (!defined('ABSPATH')) {
    exit; // Verhindert den direkten Zugriff auf das Skript, wenn nicht innerhalb von WordPress
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

// Definiert eine Klasse für das Rechnungszahlungsgateway innerhalb der WooCommerce Blocks.
class WC_Rechnung_Gateway_Blocks extends AbstractPaymentMethodType {
    public function initialize() {
        // Fügt eine Aktion hinzu, um Skripte im Frontend zu laden.
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function get_name() {
        return 'rechnung'; // Definiert den eindeutigen Namen des Zahlungsmethodentyps.
    }

    public function get_payment_method_script_handles() {
        return ['custom-payment-method']; // Gibt die Skript-Handles zurück, die von dieser Zahlungsmethode benötigt werden.
    }

    public function enqueue_assets() {
        // Registriert und reiht ein JavaScript ein, das für diese Zahlungsmethode benötigt wird.
        wp_register_script(
            'custom-payment-method',
            plugins_url('assets/js/custom-payment-method.js', __FILE__), // URL zum Skript
            array( "wp-blocks", "wp-element", "wp-components", "wp-i18n", "wp-editor", "wc-blocks-registry", "wc-settings"), // Abhängigkeiten
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/custom-payment-method.js'), // Version des Skripts als letzte Änderungszeit der Datei
            true // Im Footer laden
        );
        wp_enqueue_script('custom-payment-method'); // Skript in die Warteschlange einreihen
        wp_localize_script('custom-payment-method', 'customPaymentMethodParams', array(
            'enabled' => is_rechnung_payment_method_enabled() ? 'yes' : 'no' // Lokalisierte Daten, die anzeigen, ob die Zahlungsmethode aktiviert ist
        ));
    }
}
?>

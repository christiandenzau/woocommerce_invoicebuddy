<?php
/*
Plugin-Name: WooCommerce Rechnung Zahlungsgateway
Beschreibung: Fügt WooCommerce ein Zahlungsgateway für Rechnungen hinzu.
Version: 1.0
Autor: Christian Denzau
*/

// Sicherstellen, dass das Skript nicht direkt aufgerufen wird
if (!defined("ABSPATH")) {
  exit();
}

// Initialisiert das Plugin, sobald alle Plugins geladen sind
add_action("plugins_loaded", "init_rechnung_gateway");

function init_rechnung_gateway()
{
  error_log("woocommerce-rechnung.php gestartet");

  // Überprüft, ob die Klasse WC_Payment_Gateway existiert
  if (!class_exists("WC_Payment_Gateway")) {
    error_log("WC_Payment_Gateway Klasse nicht gefunden");
    return;
  }

  // Bindet die notwendigen PHP-Dateien ein
  require_once plugin_dir_path(__FILE__) . "class-wc-rechnung-gateway.php";
  require_once plugin_dir_path(__FILE__) .
    "class-wc-rechnung-gateway-blocks.php";

  // Fügt das neue Zahlungsgateway zu WooCommerce hinzu
  add_filter("woocommerce_payment_gateways", "add_rechnung_gateway_class");

  function add_rechnung_gateway_class($methods)
  {
    error_log("WC_Rechnung_Gateway zu WooCommerce hinzugefügt");
    $methods[] = "WC_Rechnung_Gateway";
    return $methods;
  }

  // Registriert das Zahlungsgateway innerhalb der WooCommerce Blocks
  add_action(
    "woocommerce_blocks_loaded",
    "register_rechnung_gateway_with_blocks"
  );

  function register_rechnung_gateway_with_blocks()
  {
    error_log("WC_Rechnung_Gateway mit Blöcken registriert");
    if (
      class_exists(
        "Automattic\WooCommerce\Blocks\Payments\Integrations\IntegrationRegistry"
      )
    ) {
      require_once plugin_dir_path(__FILE__) .
        "class-wc-rechnung-gateway-blocks.php";
      add_action(
        "woocommerce_blocks_payment_method_type_registration",
        function ($payment_method_registry) {
          $payment_method_registry->register(new WC_Rechnung_Gateway_Blocks());
        }
      );
    }
  }

  // Fügt JavaScript-Dateien hinzu, wenn sich der Benutzer auf der Checkout-Seite befindet
  add_action("wp_enqueue_scripts", "enqueue_rechnung_payment_method_script");

  function enqueue_rechnung_payment_method_script()
  {
    if (is_checkout()) {
      error_log("custom-payment-method.js registriert");

      $enabled = is_rechnung_payment_method_enabled() ? "yes" : "no";

      wp_register_script(
        "custom-payment-method",
        plugins_url("assets/js/custom-payment-method.js", __FILE__),
        ["wc-blocks-registry"],
        filemtime(
          plugin_dir_path(__FILE__) . "assets/js/custom-payment-method.js"
        ),
        true
      );

      wp_localize_script("custom-payment-method", "customPaymentMethodParams", [
        "enabled" => $enabled,
      ]);

      wp_enqueue_script("custom-payment-method");
      error_log("custom-payment-method.js eingereiht mit aktiviert: $enabled");
    } else {
      error_log("Nicht auf der Checkout-Seite, Skript nicht eingereiht");
    }
  }

  // Überprüft, ob die Zahlungsmethode für den aktuellen Benutzer aktiviert ist
  function is_rechnung_payment_method_enabled()
  {
    $payment_gateways = WC()->payment_gateways->payment_gateways();
    if (!isset($payment_gateways["rechnung"])) {
      error_log("custom-payment-method nicht gefunden");
      return false;
    }

    $gateway = $payment_gateways["rechnung"];
    if ("yes" !== $gateway->enabled) {
      return false;
    }

    // Überprüft, ob der aktuelle Benutzer zur Nutzung der Zahlungsmethode berechtigt ist
    $user_id = get_current_user_id();
    $allowed_users = get_option("wc_rechnung_allowed_users", []);
    return in_array($user_id, $allowed_users);
  }

  add_action("admin_menu", "add_rechnung_settings_page");

  function add_rechnung_settings_page()
  {
    add_menu_page(
      "Rechnungs&shy;freigabe", // Seitentitel
      "Rechnungs&shy;freigabe", // Menüpunkt-Titel
      "manage_options", // Berechtigung
      "rechnung_settings", // Menü-Slug
      "render_rechnung_settings_page", // Callback-Funktion
      "dashicons-media-document" // Icon-URL oder Dashicons-Klasse
    );
  }

  function render_rechnung_settings_page()
  {
    ?>
        <h1>Rechnungsfreigabe</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields("rechnung_settings");
            do_settings_sections("rechnung_settings");
            submit_button();?>
        </form>
        <?php
  }

  // Initialisiert die Einstellungen
  add_action("admin_init", "rechnung_settings_init");

  function rechnung_settings_init()
  {
    register_setting("rechnung_settings", "wc_rechnung_allowed_users", [
      "type" => "array",
      "sanitize_callback" => "sanitize_user_selection",
      "default" => [],
    ]);

    add_settings_section(
      "rechnung_settings_section",
      __("Aktiviere Rechnungszahlung für spezifische Nutzer", "woocommerce"),
      null,
      "rechnung_settings"
    );

    add_settings_field(
      "rechnung_users",
      __("Erlaubte Nutzer", "woocommerce"),
      "render_user_checkboxes",
      "rechnung_settings",
      "rechnung_settings_section"
    );
  }

  // Rendert die Checkboxes für die Nutzer
  function render_user_checkboxes()
  {
    $users = get_users(["fields" => ["ID", "display_name"]]);
    $allowed_users = get_option("wc_rechnung_allowed_users", []);

    foreach ($users as $user) {
      $checked = in_array($user->ID, $allowed_users) ? "checked" : "";
      echo "<label>";
      echo '<input type="checkbox" name="wc_rechnung_allowed_users[]" value="' .
        esc_attr($user->ID) .
        '" ' .
        $checked .
        ">";
      echo esc_html($user->display_name);
      echo "</label><br>";
    }
  }

  // Validiert die Benutzerauswahl
  function sanitize_user_selection($input)
  {
    return is_array($input) ? array_map("absint", $input) : [];
  }
}
?>

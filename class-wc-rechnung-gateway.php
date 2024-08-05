<?php
if (!defined("ABSPATH")) {
  exit(); // Verhindert den direkten Zugriff auf die Datei
}

class WC_Rechnung_Gateway extends WC_Payment_Gateway
{
  public function __construct()
  {
    $this->id = "rechnung";
    $this->method_title = __("Kauf auf Rechnung", "woocommerce");
    $this->method_description = __(
      "Ermöglicht Zahlungen per Rechnung.",
      "woocommerce"
    );

    // Einstellungen laden.
    $this->init_form_fields();
    $this->init_settings();

    // Vom Benutzer festgelegte Variablen definieren.
    $this->title = $this->get_option("title");
    $this->description = $this->get_option("description");

    // Aktionen hinzufügen.
    add_action("woocommerce_update_options_payment_gateways_" . $this->id, [
      $this,
      "process_admin_options",
    ]);

    // Weitere Aktionen können hier hinzugefügt werden.
  }

  public function init_form_fields()
  {
    $this->form_fields = [
      "enabled" => [
        "title" => __("Aktivieren/Deaktivieren", "woocommerce"),
        "type" => "checkbox",
        "label" => __("Rechnungszahlung aktivieren", "woocommerce"),
        "default" => "yes",
      ],
      "title" => [
        "title" => __("Titel", "woocommerce"),
        "type" => "text",
        "description" => __(
          "Steuert den Titel, den der Benutzer während des Checkouts sieht.",
          "woocommerce"
        ),
        "default" => __("Rechnung", "woocommerce"),
        "desc_tip" => true,
      ],
      "description" => [
        "title" => __("Beschreibung", "woocommerce"),
        "type" => "textarea",
        "description" => __(
          "Steuert die Beschreibung, die der Benutzer während des Checkouts sieht.",
          "woocommerce"
        ),
        "default" => __("Bezahlen Sie bequem per Rechnung.", "woocommerce"),
      ],
    ];
  }

  public function process_payment($order_id)
  {
    // Retrieve the order object
    $order = wc_get_order($order_id);

    // Set the order status to "pending" with a custom message in German
    $order->update_status(
      "pending",
      __("Zahlung ausstehend. Warte auf Zahlung per Rechnung.", "woocommerce")
    );

    // Reduce stock levels
    $order->reduce_order_stock();

    // Clear the cart
    WC()->cart->empty_cart();

    // Return success and redirect URL
    return [
      "result" => "success",
      "redirect" => $this->get_return_url($order),
    ];
  }
}

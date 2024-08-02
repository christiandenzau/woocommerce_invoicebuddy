(function () {
  console.log("custom-payment-method.js geladen");

  // Überprüfen, ob die notwendige Umgebung geladen ist und die Zahlungsmethode aktiviert ist
  if (typeof wc !== 'undefined' && wc.wcBlocksRegistry && typeof customPaymentMethodParams !== "undefined" && customPaymentMethodParams.enabled === "yes") {
	console.log("wc.wcBlocksRegistry und customPaymentMethodParams geladen");

	// Extrahieren der Methode zum Registrieren von Zahlungsmethoden aus wcBlocksRegistry
	const { registerPaymentMethod } = wc.wcBlocksRegistry;

	// Definition der benutzerdefinierten Zahlungsmethode "Rechnung"
	const CustomPaymentMethod = {
	  name: "rechnung", // Der eindeutige Name der Zahlungsmethode
	  label: "Rechnung", // Das Label, das dem Benutzer angezeigt wird
	  canMakePayment: () => true, // Funktion, die überprüft, ob die Zahlung möglich ist
	  content: wp.element.createElement("div", null, "Mit Rechnung bezahlen"), // Inhalt, der im Checkout angezeigt wird
	  edit: wp.element.createElement("div", null, "Rechnungszahlung konfigurieren"), // Inhalt, der bei der Bearbeitung angezeigt wird
	  ariaLabel: "Per Rechnung zahlen", // Aria-Label für Barrierefreiheit
	};

	console.log("Versuche, die benutzerdefinierte Zahlungsmethode zu registrieren");
	registerPaymentMethod(CustomPaymentMethod); // Registrierung der Zahlungsmethode
	console.log("CustomPaymentMethod erfolgreich registriert");
  } else {
	console.log("wc.wcBlocksRegistry oder customPaymentMethodParams wurden nicht richtig geladen");
  }
})();

document.addEventListener("DOMContentLoaded", function () {
  const checkbox = document.querySelector('input[name="mpp_is_locked"]');
  const priceField = document.querySelector("#mpp_price_field");
  const priceInput = document.querySelector("input[name='mpp_price']");
  const warning = document.getElementById("mpp_price_warning");

  function updatePaywallUI() {
    const isLocked = checkbox?.checked ?? false;
    const price = parseInt(priceInput?.value) || 0;
    const hasUserInput = priceInput?.value !== '';

    // Toggle price field visibility
    if (priceField) priceField.style.display = isLocked ? "block" : "none";

    // Show warning only if locked, user has typed, AND price is invalid
    if (warning) warning.style.display = (isLocked && hasUserInput && price <= 0) ? "block" : "none";
  }

  // Initial check on page load
  updatePaywallUI();

  // Update UI on checkbox change
  if (checkbox) checkbox.addEventListener("change", updatePaywallUI);
  
  // Update UI on price input
  if (priceInput) priceInput.addEventListener("input", updatePaywallUI);
});

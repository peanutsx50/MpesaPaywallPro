document.addEventListener("DOMContentLoaded", function () {
  const checkbox = document.querySelector('input[name="mpp_is_locked"]');
  const price = document.getElementById("mpp_price");

  function togglePriceField() {
    if (checkbox.checked) {
      price.style.display = "block";
    } else {
      price.style.display = "none";
    }
  }

  // Initial check on page load
  togglePriceField();

  // Add event listener for checkbox change
  checkbox.addEventListener("change", togglePriceField);
});

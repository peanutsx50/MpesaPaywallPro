document.addEventListener("DOMContentLoaded", function () {
  // Check if cookie is set to hide notice
  const closedNotice = get_cookie("mpesapaywallpro_notice_closed");
  const notice = document.querySelector(".mpesapaywallpro-notice");

  // admin test connection elements
  const testButton = document.getElementById("test-mpesa-connection");
  const phoneInput = document.getElementById("test_phone_number");
  const resultDiv = document.getElementById("test-connection-result");

  if (!closedNotice && notice) {
    notice.style.display = "flex";
  }

  // Close button functionality for the notice
  const closeBtn = document.querySelector(".mpesapaywallpro-notice-close");

  // Add event listener to close button
  if (closeBtn && notice) {
    closeBtn.addEventListener("click", function () {
      notice.style.display = "none";
      document.cookie =
        "mpesapaywallpro_notice_closed=true; path=/; max-age=" +
        60 * 60 * 24 * 30; // 30 days
    });
  }

  // Test Connection button functionality
  if (testButton && phoneInput && resultDiv) {
    testButton.addEventListener("click", function () {
      // Get and validate phone number
      const phoneValue = phoneInput.value.trim();

      // Check if phone number is empty
      if (!phoneValue) {
        resultDiv.style.display = "block";
        resultDiv.classList.add("error");
        resultDiv.innerHTML =
          '<span class="dashicons dashicons-no"></span> Please enter a phone number.';
        return;
      }

      // Validate phone number
      const phoneNumber = validatePhoneNumber(phoneValue);

      if (!phoneNumber) {
        resultDiv.style.display = "block";
        resultDiv.classList.add("error");
        resultDiv.innerHTML =
          '<span class="dashicons dashicons-no"></span> Invalid Kenyan phone number. Please use format: 254XXXXXXXXX, +254XXXXXXXXX, or 07XXXXXXXX';
        return;
      }

      // Proceed with test if validation passes
      testButton.disabled = true;
      testButton.innerHTML = "Testing...";
      testConnection(phoneNumber, testButton, phoneInput, resultDiv);
    });
  }

  function validatePhoneNumber(phoneNumber) {
    // Remove any whitespace
    let cleaned = phoneNumber.trim().replace(/\s+/g, "");

    // Remove leading + if present
    if (cleaned.startsWith("+")) {
      cleaned = cleaned.substring(1);
    }

    // Replace 07 with 254 at the start
    if (cleaned.startsWith("07")) {
      cleaned = "254" + cleaned.substring(1);
    }

    // Check if it's a valid Kenyan number starting with 254
    // Valid Kenyan mobile prefixes: 254(7XX or 1XX) followed by 7 more digits
    const phonePattern = /^254(7[0-9]{8}|1[0-9]{8})$/;

    if (!phonePattern.test(cleaned)) {
      return false;
    }

    return cleaned; // Return the formatted number instead of just true
  }
});

// Function to get cookie
function get_cookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}

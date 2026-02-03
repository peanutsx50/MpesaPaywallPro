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

      // clean phone number
      const phoneNumber = cleanPhoneNumber(phoneValue);

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
      testConnection(phoneNumber, testButton, resultDiv);
    });
  }

  function cleanPhoneNumber(phone) {
    // Input validation
    if (!phone || typeof phone !== "string") return false;

    let cleaned = phone.trim().replace(/[\s\-\+]/g, "");

    // Length constraints
    if (cleaned.length < 10 || cleaned.length > 20) return false;

    // Handle different input formats
    if (cleaned.startsWith("+254")) {
      cleaned = cleaned.substring(1); // Remove +
    } else if (cleaned.startsWith("07")) {
      cleaned = "254" + cleaned.substring(1); // Convert 07 to 254
    }

    // Validate Kenyan number format
    const phonePattern = /^254(?:7[01][0-9]|10[0-9]|11[0-9])[0-9]{6}$/;

    if (!phonePattern.test(cleaned)) {
      return false;
    }

    return cleaned; // Return normalized number
  }
});

// Function to get cookie
function get_cookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}

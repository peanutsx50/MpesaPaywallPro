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
  if (testButton && !empty(phoneInput.value)) {
    testButton.addEventListener("click", async function () {
      testButton.disabled = true;
      testButton.innerHTML = "Testing...";
      testConnection(testButton, phoneInput, resultDiv);
    });
  };

});

// Function to get cookie
function get_cookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}
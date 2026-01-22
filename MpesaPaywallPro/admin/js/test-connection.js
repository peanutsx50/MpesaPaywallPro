function displayConnectionError(testButton, phoneInput, resultDiv, message) {
  testButton.disabled = false;
  testButton.innerHTML = "Transaction Failed. Try Again";
  phoneInput.classList.add("mpp-error");
  resultDiv.textContent = message;
  resultDiv.classList.add("mpp-visible");
}

async function testConnection(testButton, phoneInput, resultDiv) {
    // test mpesa connection
     try {
    const response = await fetch(mpp_admin_ajax_object.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      body: new URLSearchParams({
        action: "mpp_admin_test_connection",
        phone_number: mpp_admin_ajax_object.phone_number,
        mpp_nonce: mpp_admin_ajax_object.nonce,
        amount: 1,
      }),
    });

    const data = await response.json();

    if (data.success) {
      console.log("Payment initiated:", data);
      testButton.disabled = false;
    } else {
      const errorMessage = data.data?.message || "Payment initiation failed";
      console.error("Payment initiation failed:", errorMessage);
      displayConnectionError(testButton, phoneInput, resultDiv, errorMessage);
    }
  } catch (error) {
    console.error("Error initiating payment:", error);
    displayConnectionError(testButton, phoneInput, resultDiv, "An error occurred. Please try again.");
  }
}
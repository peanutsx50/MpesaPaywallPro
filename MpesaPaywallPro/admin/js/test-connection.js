function displayConnectionError(testButton, resultDiv, message) {
  testButton.disabled = false;
  testButton.innerHTML = "Transaction Failed. Try Again";
  resultDiv.textContent = message;
  resultDiv.classList.add("mpp-visible");
}

async function testConnection(phoneNumber, testButton, resultDiv) {
    // test mpesa connection
     try {
    const response = await fetch(mpp_admin_ajax_object.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      body: new URLSearchParams({
        action: "mpp_admin_test_connection",
        phone_number: phoneNumber,
        mpp_nonce: mpp_admin_ajax_object.nonce,
        amount: 1,
      }),
    });

    const data = await response.json();

    if (data.success) {
      testButton.disabled = false;
      testButton.innerHTML = "Transaction Initiated Successfully";
      resultDiv.textContent =
        "Payment initiation successful. Please check your phone to complete the transaction.";
      resultDiv.classList.add("mpp-visible");
    } else {
      const errorMessage = data.data?.message || "Payment initiation failed";
      console.error("Payment initiation failed:", errorMessage);
      displayConnectionError(testButton, resultDiv, errorMessage); 
    }
  } catch (error) {
    console.error("Error initiating payment:", error);
    displayConnectionError(testButton, resultDiv, "An error occurred. Please try again.");
  }
}
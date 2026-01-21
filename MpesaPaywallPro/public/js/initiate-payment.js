function displayPaymentError(submitBtn, phoneInput, errorMsg, message) {
  submitBtn.disabled = false;
  submitBtn.innerHTML = "Transaction Failed. Try Again";
  phoneInput.classList.add("mpp-error");
  errorMsg.textContent = message;
  errorMsg.classList.add("mpp-visible");
}

/**
 * Initiates an M-Pesa payment request via AJAX
 * @param {string} phoneNumber - Customer's phone number
 * @param {HTMLElement} submitBtn - The submit button element
 * @param {HTMLElement} phoneInput - The phone input element
 * @param {HTMLElement} errorMsg - The error message element
 */
async function initiatePayment(phoneNumber, submitBtn, phoneInput, errorMsg) {
  try {
    const response = await fetch(mpp_ajax_object.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      body: new URLSearchParams({
        action: "mpp_process_payment",
        phone_number: phoneNumber,
        mpp_nonce: mpp_ajax_object.nonce,
      }),
    });

    const data = await response.json();

    if (data.success) {
      console.log("Payment initiated:", data);
      checkPaymentStatus(data.data.response.CheckoutRequestID);
    } else {
      const errorMessage = data.data?.message || "Payment initiation failed";
      console.error("Payment initiation failed:", errorMessage);
      displayPaymentError(submitBtn, phoneInput, errorMsg, errorMessage);
    }
  } catch (error) {
    console.error("Error initiating payment:", error);
    displayPaymentError(submitBtn, phoneInput, errorMsg, "An error occurred. Please try again.");
  }
}

function displayPaymentError(submitBtn, phoneInput, errorMsg, message) {
  submitBtn.disabled = false;
  submitBtn.innerHTML = "Transaction Failed. Try Again";
  phoneInput.classList.add("mpp-error");
  errorMsg.textContent = message;
  errorMsg.classList.add("mpp-visible");
}

function displayVerifying(submitBtn, phoneInput, errorMsg) {
  submitBtn.disabled = true;
  submitBtn.innerHTML = "Verifying payment...";
  phoneInput.classList.remove("mpp-error");
  errorMsg.classList.remove("mpp-visible");
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
    const response = await fetch(mpp_ajax_object.process_payment_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        phone_number: phoneNumber,
        amount: mpp_ajax_object.amount,
        nonce: mpp_ajax_object.nonce,
      }),
    });

    const text = await response.text();

    const data = JSON.parse(text);

    if (data.success) {
      checkPaymentStatus(data.data.checkout_request_id, submitBtn, phoneNumber);
      displayVerifying(submitBtn, phoneInput, errorMsg);

    } else {
      const errorMessage = data.data?.message || "Payment initiation failed";
      displayPaymentError(submitBtn, phoneInput, errorMsg, errorMessage);
    }
  } catch (error) {
    displayPaymentError(
      submitBtn,
      phoneInput,
      errorMsg,
      "An error occurred. Please try again.",
    );
  }
}

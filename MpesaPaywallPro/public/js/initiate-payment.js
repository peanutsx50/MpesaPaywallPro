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
  const { process_payment_url, post_id, amount, nonce } = mpp_ajax_object;

  // Validate required fields
  if (!phoneNumber || !post_id || !amount || !nonce) {
    displayPaymentError(
      submitBtn,
      phoneInput,
      errorMsg,
      "Missing required payment information. Please refresh the page and try again.",
    );
    return;
  }

  try {
    const response = await fetch(process_payment_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        phone_number: phoneNumber,
        post_id: post_id,
        amount: amount,
        nonce: nonce,
      }),
    });

    // Handle HTTP errors
    if (!response.ok) {
      throw new Error(`Server error: ${response.status}`);
    }

    const data = await response.json();

    if (data.success) {
      displayVerifying(submitBtn, phoneInput, errorMsg);
      checkPaymentStatus(data.data.checkout_request_id, submitBtn, phoneNumber);
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

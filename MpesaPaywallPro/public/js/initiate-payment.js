function initiatePayment(phoneNumber, submitBtn, phoneInput, errorMsg) {
  // AJAX call to initiate payment
  fetch(mpp_ajax_object.ajax_url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
    },
    body: new URLSearchParams({
      action: "mpp_process_payment",
      phone_number: phoneNumber,
      mpp_nonce: mpp_ajax_object.nonce,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        console.log("Payment initiated:", data);
        // You can store transaction ID or other info if needed
        checkPaymentStatus(data.data.response.CheckoutRequestID)
      } else {
        console.error("Payment initiation failed:", data.data.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = "Transaction Failed. Try Again";
        phoneInput.classList.add("mpp-error");
        errorMsg.textContent =
          "Payment initiation failed: " + data.data.message;
        errorMsg.classList.add("mpp-visible");
      }
    })
    .catch((error) => {
      console.error("Error initiating payment:", error);
    });
}

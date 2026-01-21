function initiatePayment(phoneNumber) {
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
      } else {
        console.error("Payment initiation failed:", data.message);
      }
    })
    .catch((error) => {
      console.error("Error initiating payment:", error);
    });
}
function checkPaymentStatus(transactionId) {
  // TODO: Implement polling logic to check payment status
}

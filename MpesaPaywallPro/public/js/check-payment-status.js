async function checkPaymentStatus(
  checkoutRequestId,
  submitBtn,
  maxAttempts = 20,
  pollInterval = 3000,
) {
  console.log("Checking payment status for:", checkoutRequestId);

  let pollCount = 0;

  while (pollCount < maxAttempts) {
    pollCount++;

    try {
      const response = await fetch(
        `${mpp_ajax_object.callback_url}?checkout_id=${checkoutRequestId}`,
        { method: "GET", credentials: "same-origin" },
      );

      const data = await response.json();

      if (data.status === "success") {
        console.log("Payment successful:", data);
        submitBtn.disabled = false;
        submitBtn.innerHTML = "Payment Complete ✓";
        submitBtn.style.backgroundColor = "#4CAF50";
        return data;
      }
    } catch (error) {
      console.warn(`Poll attempt ${pollCount} failed:`, error);
    }

    // sleep for pollInterval before next attempt
    if (pollCount < maxAttempts) {
      await new Promise((resolve) => setTimeout(resolve, pollInterval));
    }
  }

  // Max polls exceeded
  console.error("Payment verification timeout after", maxAttempts, "attempts");
  submitBtn.disabled = false;
  submitBtn.innerHTML = "Payment timeout. Please try again.";
  submitBtn.style.backgroundColor = "#ff9800";
}

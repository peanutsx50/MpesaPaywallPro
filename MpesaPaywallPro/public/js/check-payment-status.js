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
        `${mpp_ajax_object.callback_url}?checkout_id=${checkoutRequestId}&phone=${phoneNumber}`,
        { method: "GET", credentials: "same-origin" },
      );

      const data = await response.json();

      if (data.status === "success") {
        console.log("Payment successful:", data);
        submitBtn.disabled = false;
        submitBtn.innerHTML = "Payment Complete ✓";
        submitBtn.style.backgroundColor = "#4CAF50";
        //set cookie to indicate payment
        document.cookie = `mpp_paid_${checkoutRequestId}=true; max-age=${
          mpp_ajax_object.access_expiry * 86400
        }; path=/`;
        return data;
      }

      if (data.status === "failed") {
        submitBtn.disabled = false;
        submitBtn.innerHTML = data.message || "Payment cancelled";
        submitBtn.style.backgroundColor = "#f44336";
        console.warn("Payment failed:", data);
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

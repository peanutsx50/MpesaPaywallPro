async function checkPaymentStatus(
  checkoutRequestId,
  submitBtn,
  phoneNumber,
  maxAttempts = 20,
  pollInterval = 3000,
) {
  console.log("Checking payment status for:", checkoutRequestId);

  let pollCount = 0;
  let continuePolling = true;

  while (pollCount < maxAttempts && continuePolling) {
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
        document.cookie = `mpp_paid_${mpp_ajax_object.post_id}=${checkoutRequestId}; max-age=${
          mpp_ajax_object.access_expiry * 86400
        }; path=/; SameSite=Strict`;

        // Show success message
        setTimeout(() => {
          submitBtn.innerHTML = "Unlocking Content...";
          // Reload page to show unlocked content
          window.location.reload();
        }, 1500);
        
        continuePolling = false; // Stop polling
        return; // Exit function
      }

      if (data.status === "failed") {
        submitBtn.disabled = false;
        submitBtn.innerHTML = data.message || "Payment cancelled";
        submitBtn.style.backgroundColor = "#f44336";
        console.warn("Payment failed:", data);
        
        continuePolling = false; // Stop polling
        return; // Exit function
      }
      
    } catch (error) {
      console.warn(`Poll attempt ${pollCount} failed:`, error);
    }

    // Wait for pollInterval before next attempt
    if (pollCount < maxAttempts && continuePolling) {
      await new Promise((resolve) => setTimeout(resolve, pollInterval));
    }
  }

  // Only reach here if max attempts exceeded without success or failure
  if (continuePolling) {
    console.error("Payment verification timeout after", maxAttempts, "attempts");
    submitBtn.disabled = false;
    submitBtn.innerHTML = "Payment timeout. Please try again.";
    submitBtn.style.backgroundColor = "#ff9800";
  }
}
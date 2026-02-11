async function checkPaymentStatus(
  checkoutRequestId,
  submitBtn,
  maxAttempts = mpp_ajax_object.maxPollAttempts,
  pollInterval = mpp_ajax_object.pollInterval,
) {
  let pollCount = 0;
  let continuePolling = true;

  while (pollCount < maxAttempts && continuePolling) {
    pollCount++;

    try {
      const response = await fetch(`${mpp_ajax_object.confirm_payment_url}`, {
        method: "POST",
        headers: {
        "Content-Type": "application/json",
      },
        body: JSON.stringify({
          checkout_id: checkoutRequestId,
          locked_post_id: mpp_ajax_object.post_id,
          nonce: mpp_ajax_object.nonce,
        }),
      });

      const data = await response.json();

      if (data.status === "success") {
        submitBtn.disabled = false;
        submitBtn.innerHTML = "Payment Complete ✓";
        submitBtn.style.backgroundColor = "#4CAF50";

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
        continuePolling = false; // Stop polling
        return; // Exit function
      }
    } catch (error) {
      // display error message and stop polling
      submitBtn.disabled = false;
      submitBtn.innerHTML = "Error checking payment";
      submitBtn.style.backgroundColor = "#f44336";
      continuePolling = false; // Stop polling
      return; // Exit function
    }

    // Wait for pollInterval before next attempt
    if (pollCount < maxAttempts && continuePolling) {
      await new Promise((resolve) => setTimeout(resolve, pollInterval));
    }
  }

  // Only reach here if max attempts exceeded without success or failure
  if (continuePolling) {
    submitBtn.disabled = false;
    submitBtn.innerHTML = "Payment timeout. Please try again.";
    submitBtn.style.backgroundColor = "#ff9800";
  }
}

document.addEventListener("DOMContentLoaded", function () {
  // modal js functions goes here
  const modal = document.getElementById("mpp-phone-modal");
  const openBtn = document.getElementById("mpp-pay-button");
  const closeBtn = document.getElementById("mpp-modal-close");
  const cancelBtn = document.getElementById("mpp-cancel-btn");
  const form = document.getElementById("mpp-phone-form");
  const phoneInput = document.getElementById("mpp-phone-number");
  const submitBtn = document.getElementById("mpp-submit-btn");
  const errorMsg = document.getElementById("mpp-phone-error");

  // Open modal
  if (openBtn) {
    openBtn.addEventListener("click", function () {
      modal.style.display = "flex";
      phoneInput.focus();
    });
  }

  // Close modal function
  function closeModal() {
    modal.classList.add("mpp-fade-out");
    setTimeout(() => {
      modal.classList.remove("mpp-fade-out");
      modal.style.display = "none";
    }, 200);
    form.reset();
    phoneInput.classList.remove("mpp-error");
    errorMsg.classList.remove("mpp-visible");
  }

  // Close modal events
  closeBtn.addEventListener("click", closeModal);
  cancelBtn.addEventListener("click", closeModal);

  // Close on overlay click
  modal.addEventListener("click", function (e) {
    if (e.target === modal) {
      closeModal();
    }
  });

  // Close on Escape key
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && modal.style.display === "flex") {
      closeModal();
    }
  });

  // Validate phone number
  function validatePhone(phone) {
    // Remove spaces and dashes
    phone = phone.replace(/[\s-]/g, "");

    // Check if it's a valid Kenyan number (starts with 07 or 01, 10 digits)
    const phonePattern = /^254(?:7[0-9]|1[01])[0-9]{7}$/;
    return phonePattern.test(phone);
  }

  // Format phone number for display
  phoneInput.addEventListener("input", function (e) {
    // Remove non-numeric characters
    let value = e.target.value.replace(/\D/g, "");
    e.target.value = value;
  });

  // Handle form submission
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const phoneNumber = phoneInput.value.trim().replace(/^\+/, "");

    // Validate
    if (!validatePhone(phoneNumber)) {
      phoneInput.classList.add("mpp-error");
      errorMsg.textContent =
        "please enter a valid Kenyan phone number (e.g., 254712345678)";
      errorMsg.classList.add("mpp-visible");
      return;
    }

    // Clear errors
    phoneInput.classList.remove("mpp-error");
    errorMsg.classList.remove("mpp-visible");

    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML =
      '<span class="mpp-loading-spinner"></span>Processing...';

    // TODO: Send to your payment endpoint
    // Example AJAX call:
    initiatePayment(phoneNumber);
  });

  function initiatePayment(phoneNumber) {
    // Example AJAX call to initiate payment
  }

  function checkPaymentStatus(transactionId) {
    // TODO: Implement polling logic to check payment status
  }
});

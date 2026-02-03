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
  function cleanPhoneNumber(phone) {
    // Input validation
    if (!phone || typeof phone !== "string") return false;

    let cleaned = phone.trim().replace(/[\s\-\+]/g, "");

    // Length constraints
    if (cleaned.length < 10 || cleaned.length > 20) return false;

    // Handle different input formats
    if (cleaned.startsWith("+254")) {
      cleaned = cleaned.substring(1); // Remove +
    } else if (cleaned.startsWith("07")) {
      cleaned = "254" + cleaned.substring(1); // Convert 07 to 254
    }

    // Validate Kenyan number format
    const phonePattern = /^254(?:7[01][0-9]|10[0-9]|11[0-9])[0-9]{6}$/;

    if (!phonePattern.test(cleaned)) {
      return false;
    }

    return cleaned; // Return normalized number
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
    
    // clean phone number
    const phoneNumber = cleanPhoneNumber(phoneInput.value);

    if (!phoneNumber) {
      phoneInput.classList.add("mpp-error");
      errorMsg.textContent =
        "Invalid Kenyan phone number. Please use format: 254XXXXXXXXX, +254XXXXXXXXX, or 07XXXXXXXX";
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

    // intiate payment then check payment status
    initiatePayment(phoneNumber, submitBtn, phoneInput, errorMsg);
  });
});

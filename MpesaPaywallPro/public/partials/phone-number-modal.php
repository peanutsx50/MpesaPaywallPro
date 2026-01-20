<?php

/**
 * The phone number modal partial file.
 * This file is responsible for rendering the phone number
 * input modal in the public-facing side of the website.
 * 
 * @since    1.0.0
 * @package  MpesaPaywallPro
 * 
 * @wordpress-public
 * @subpackage MpesaPaywallPro/public/partials
 * 
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}
?>
<!-- Modal HTML -->
<div class="mpp-modal-overlay" id="mpp-phone-modal">
    <div class="mpp-modal">
        <button class="mpp-modal-close" id="mpp-modal-close" aria-label="Close">&times;</button>

        <div class="mpp-modal-header">
            <h3 class="mpp-modal-title"><?php _e('Enter your M-Pesa number', 'mpesapaywallpro'); ?></h3>
            <p class="mpp-modal-subtitle">
                <?php echo sprintf(__('You\'ll receive an M-Pesa prompt to pay KES %s', 'mpesapaywallpro'), esc_html($price)); ?>
            </p>
        </div>

        <form id="mpp-phone-form">
            <div class="mpp-form-group">
                <label class="mpp-form-label" for="mpp-phone-number">
                    <?php _e('Phone Number', 'mpesapaywallpro'); ?>
                </label>
                <input
                    type="tel"
                    id="mpp-phone-number"
                    class="mpp-phone-input"
                    placeholder="0712345678"
                    maxlength="10"
                    required />
                <p class="mpp-phone-hint"><?php _e('Enter your Safaricom number (e.g., 0712345678)', 'mpesapaywallpro'); ?></p>
                <p class="mpp-error-message" id="mpp-phone-error"></p>
            </div>

            <div class="mpp-modal-actions">
                <button type="button" class="mpp-btn mpp-btn-secondary" id="mpp-cancel-btn">
                    <?php _e('Cancel', 'mpesapaywallpro'); ?>
                </button>
                <button type="submit" class="mpp-btn mpp-btn-primary" id="mpp-submit-btn">
                    <?php _e('Continue', 'mpesapaywallpro'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        const modal = document.getElementById('mpp-phone-modal');
        const openBtn = document.getElementById('mpp-pay-button');
        const closeBtn = document.getElementById('mpp-modal-close');
        const cancelBtn = document.getElementById('mpp-cancel-btn');
        const form = document.getElementById('mpp-phone-form');
        const phoneInput = document.getElementById('mpp-phone-number');
        const submitBtn = document.getElementById('mpp-submit-btn');
        const errorMsg = document.getElementById('mpp-phone-error');

        // Open modal
        if (openBtn) {
            openBtn.addEventListener('click', function() {
                modal.style.display = 'flex';
                phoneInput.focus();
            });
        }

        // Close modal function
        function closeModal() {
            modal.style.display = 'none';
            form.reset();
            phoneInput.classList.remove('mpp-error');
            errorMsg.classList.remove('mpp-visible');
        }

        // Close modal events
        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        // Close on overlay click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('mpp-active')) {
                closeModal();
            }
        });

        // Validate phone number
        function validatePhone(phone) {
            // Remove spaces and dashes
            phone = phone.replace(/[\s-]/g, '');

            // Check if it's a valid Kenyan number (starts with 07 or 01, 10 digits)
            const phoneRegex = /^(07|01)\d{8}$/;
            return phoneRegex.test(phone);
        }

        // Format phone number for display
        phoneInput.addEventListener('input', function(e) {
            // Remove non-numeric characters
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });

        // Handle form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const phoneNumber = phoneInput.value.trim();

            // Validate
            if (!validatePhone(phoneNumber)) {
                phoneInput.classList.add('mpp-error');
                errorMsg.textContent = '<?php _e('Please enter a valid Kenyan phone number (e.g., 0712345678)', 'mpesapaywallpro'); ?>';
                errorMsg.classList.add('mpp-visible');
                return;
            }

            // Clear errors
            phoneInput.classList.remove('mpp-error');
            errorMsg.classList.remove('mpp-visible');

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="mpp-loading-spinner"></span><?php _e('Processing...', 'mpesapaywallpro'); ?>';

            // TODO: Send to your payment endpoint
            // Example AJAX call:
            initiatePayment(phoneNumber);
        });

        function initiatePayment(phoneNumber) {
            // AJAX call to your WordPress endpoint
            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'mpp_initiate_payment',
                        phone: phoneNumber,
                        post_id: '<?php echo get_the_ID(); ?>',
                        nonce: '<?php echo wp_create_nonce('mpp_payment_nonce'); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        closeModal();

                        // Show success message
                        const statusDiv = document.getElementById('mpp-payment-status');
                        if (statusDiv) {
                            statusDiv.className = 'mpp-status-visible mpp-processing';
                            statusDiv.textContent = '<?php _e('Check your phone for the M-Pesa prompt...', 'mpesapaywallpro'); ?>';
                        }

                        // TODO: Poll for payment confirmation
                        checkPaymentStatus(data.transaction_id);
                    } else {
                        throw new Error(data.message || 'Payment failed');
                    }
                })
                .catch(error => {
                    errorMsg.textContent = error.message || '<?php _e('An error occurred. Please try again.', 'mpesapaywallpro'); ?>';
                    errorMsg.classList.add('mpp-visible');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '<?php _e('Continue', 'mpesapaywallpro'); ?>';
                });
        }

        function checkPaymentStatus(transactionId) {
            // TODO: Implement polling logic to check payment status
            // You can use setInterval to check every few seconds
            console.log('Checking payment status for:', transactionId);
        }
    })();
</script>
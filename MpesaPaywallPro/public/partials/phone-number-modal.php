<style>
    .mpp-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9998;
        animation: mpp-fadeIn 0.2s ease;
    }

    .mpp-modal-overlay.mpp-active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @keyframes mpp-fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .mpp-modal {
        background: #ffffff;
        border-radius: 12px;
        padding: 32px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: mpp-slideUp 0.3s ease;
        position: relative;
        z-index: 9999;
    }

    @keyframes mpp-slideUp {
        from { 
            opacity: 0;
            transform: translateY(20px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }

    .mpp-modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        background: none;
        border: none;
        font-size: 24px;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        line-height: 1;
        transition: color 0.2s ease;
    }

    .mpp-modal-close:hover {
        color: #374151;
    }

    .mpp-modal-header {
        margin-bottom: 24px;
    }

    .mpp-modal-title {
        color: #111827;
        font-size: 22px;
        font-weight: 600;
        margin: 0 0 8px 0;
    }

    .mpp-modal-subtitle {
        color: #6b7280;
        font-size: 14px;
        margin: 0;
        line-height: 1.5;
    }

    .mpp-form-group {
        margin-bottom: 20px;
    }

    .mpp-form-label {
        display: block;
        color: #374151;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .mpp-phone-input {
        width: 100%;
        padding: 12px 16px;
        font-size: 16px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        box-sizing: border-box;
        transition: border-color 0.2s ease;
    }

    .mpp-phone-input:focus {
        outline: none;
        border-color: #111827;
    }

    .mpp-phone-input.mpp-error {
        border-color: #dc2626;
    }

    .mpp-phone-hint {
        color: #6b7280;
        font-size: 13px;
        margin-top: 6px;
    }

    .mpp-error-message {
        color: #dc2626;
        font-size: 13px;
        margin-top: 6px;
        display: none;
    }

    .mpp-error-message.mpp-visible {
        display: block;
    }

    .mpp-modal-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .mpp-btn {
        flex: 1;
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 500;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
    }

    .mpp-btn-primary {
        background: #111827;
        color: #ffffff;
    }

    .mpp-btn-primary:hover {
        background: #1f2937;
    }

    .mpp-btn-primary:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }

    .mpp-btn-secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .mpp-btn-secondary:hover {
        background: #e5e7eb;
    }

    .mpp-loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-top-color: transparent;
        border-radius: 50%;
        animation: mpp-spin 0.6s linear infinite;
        margin-right: 8px;
    }

    @keyframes mpp-spin {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
        .mpp-modal {
            padding: 24px;
            max-width: 90%;
        }

        .mpp-modal-title {
            font-size: 20px;
        }
    }
</style>

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
                    required
                />
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
            modal.classList.add('mpp-active');
            phoneInput.focus();
        });
    }

    // Close modal function
    function closeModal() {
        modal.classList.remove('mpp-active');
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
// Booking Actions - SweetAlert2 Implementation
console.log('booking-actions.js loaded successfully');

function checkInBooking(bookingId) {
    console.log('checkInBooking called with ID:', bookingId);
    
    if (typeof Swal === 'undefined') {
        alert('SweetAlert2 is not loaded! Please refresh the page.');
        return;
    }
    
    // Blur any focused element before opening modal (fixes accessibility warning)
    if (document.activeElement) {
        document.activeElement.blur();
    }
    
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to check in this guest?",
        icon: 'warning',
        iconColor: '#f59e0b',
        showCancelButton: true,
        confirmButtonColor: '#0071c2',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, check in!',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'swal-custom-popup',
            title: 'swal-custom-title',
            confirmButton: 'swal-custom-confirm',
            cancelButton: 'swal-custom-cancel'
        },
        buttonsStyling: true,
        reverseButtons: false,
        didOpen: () => {
            // Focus the confirm button when modal opens
            const confirmButton = Swal.getConfirmButton();
            if (confirmButton) {
                setTimeout(() => confirmButton.focus(), 100);
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Get CSRF token from meta tag or create it
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            
            fetch(`/admin/booking/${bookingId}/check-in`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Guest checked in successfully!',
                        icon: 'success',
                        iconColor: '#16a34a',
                        confirmButtonColor: '#0071c2',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to check in guest',
                        icon: 'error',
                        iconColor: '#dc2626',
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while checking in',
                    icon: 'error',
                    iconColor: '#dc2626',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

function checkOutBooking(bookingId) {
    console.log('checkOutBooking called with ID:', bookingId);
    
    if (typeof Swal === 'undefined') {
        alert('SweetAlert2 is not loaded! Please refresh the page.');
        return;
    }
    
    // Blur any focused element before opening modal (fixes accessibility warning)
    if (document.activeElement) {
        document.activeElement.blur();
    }
    
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to check out this guest?",
        icon: 'warning',
        iconColor: '#f59e0b',
        showCancelButton: true,
        confirmButtonColor: '#0071c2',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, check out!',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'swal-custom-popup',
            title: 'swal-custom-title',
            confirmButton: 'swal-custom-confirm',
            cancelButton: 'swal-custom-cancel'
        },
        buttonsStyling: true,
        reverseButtons: false,
        didOpen: () => {
            // Focus the confirm button when modal opens
            const confirmButton = Swal.getConfirmButton();
            if (confirmButton) {
                setTimeout(() => confirmButton.focus(), 100);
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            
            fetch(`/admin/booking/${bookingId}/check-out`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Guest checked out successfully!',
                        icon: 'success',
                        iconColor: '#16a34a',
                        confirmButtonColor: '#0071c2',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to check out guest',
                        icon: 'error',
                        iconColor: '#dc2626',
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while checking out',
                    icon: 'error',
                    iconColor: '#dc2626',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

function cancelBooking(bookingId) {
    console.log('cancelBooking called with ID:', bookingId);
    
    if (typeof Swal === 'undefined') {
        alert('SweetAlert2 is not loaded! Please refresh the page.');
        return;
    }
    
    // Blur any focused element before opening modal
    if (document.activeElement) {
        document.activeElement.blur();
    }
    
    Swal.fire({
        title: 'Cancel Booking',
        text: 'Enter cancellation reason:',
        input: 'textarea',
        inputPlaceholder: 'Enter reason for cancellation...',
        icon: 'warning',
        iconColor: '#f59e0b',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, cancel booking!',
        cancelButtonText: 'No, keep it',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to enter a cancellation reason!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            
            fetch(`/admin/booking/${bookingId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason: result.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Cancelled!',
                        text: 'Booking cancelled successfully!',
                        icon: 'success',
                        iconColor: '#16a34a',
                        confirmButtonColor: '#0071c2',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to cancel booking',
                        icon: 'error',
                        iconColor: '#dc2626',
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred',
                    icon: 'error',
                    iconColor: '#dc2626',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

function modifyBooking(bookingId) {
    Swal.fire({
        title: 'Coming Soon',
        text: 'Modify booking feature will be available soon!\n\nThis will allow you to:\n• Change dates\n• Change room type\n• Update guest count\n• Modify special requests',
        icon: 'info',
        iconColor: '#0071c2',
        confirmButtonColor: '#0071c2',
        confirmButtonText: 'OK'
    });
}

function emailGuest(email, name) {
    // Get booking reference from the page
    const bookingRef = document.querySelector('.booking-ref-large')?.textContent || 'Your Booking';
    const propertyName = document.querySelector('.detail-value')?.textContent || 'Our Hotel';
    
    const subject = encodeURIComponent(`Booking Confirmation - ${bookingRef}`);
    const body = encodeURIComponent(`Dear ${name},\n\nThank you for your booking.\n\nBooking Reference: ${bookingRef}\nProperty: ${propertyName}\n\nWe look forward to welcoming you!\n\nBest regards,\n${propertyName} Team`);
    window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
}

function printBooking() {
    window.print();
}

<div class="review-detail-card">
    <div class="review-header">
        <div class="guest-info">
            <div class="guest-avatar">{{ strtoupper(substr($review->guest_name, 0, 2)) }}</div>
            <div>
                <div class="guest-name">{{ $review->guest_name }}</div>
                <div class="guest-email">{{ $review->guest_email }}</div>
            </div>
        </div>
        <div class="review-rating">
            @for($i = 1; $i <= 5; $i++)
                <span class="star {{ $i <= $review->rating ? 'filled' : '' }}">★</span>
            @endfor
            <span class="rating-number">{{ $review->rating }}/5</span>
        </div>
    </div>

    <div class="review-meta">
        <div class="meta-item">
            <span class="meta-label">Property:</span>
            <span class="meta-value">{{ $review->property->name }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Status:</span>
            <span class="status-badge status-{{ $review->status }}">
                {{ ucfirst($review->status) }}
            </span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Submitted:</span>
            <span class="meta-value">{{ $review->created_at->format('M d, Y \a\t h:i A') }}</span>
        </div>
    </div>

    <div class="review-comment-section">
        <strong>Guest Review:</strong>
        <p class="review-text">{{ $review->comment }}</p>
    </div>

    @if($review->staff_reply)
    <div class="staff-reply-section">
        <div class="reply-header">
            <strong>Staff Reply:</strong>
            <span class="reply-date">{{ $review->replied_at->diffForHumans() }}</span>
        </div>
        <p class="reply-text">{{ $review->staff_reply }}</p>
    </div>
    @else
    <div class="no-reply-section">
        <p class="no-reply-text">No staff reply yet</p>
    </div>
    @endif

    @if($review->rejection_reason)
    <div class="rejection-section">
        <strong>Rejection Reason:</strong>
        <p class="rejection-text">{{ $review->rejection_reason }}</p>
        <small class="text-muted">Rejected {{ $review->rejected_at->diffForHumans() }}</small>
    </div>
    @endif

    <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="closeViewModal()">Close</button>
        @if($review->status === 'pending')
        <button type="button" class="btn btn-success" onclick="closeViewModal(); approveReview({{ $review->id }})">
            Approve
        </button>
        <button type="button" class="btn btn-danger" onclick="closeViewModal(); openRejectModal({{ $review->id }})">
            Reject
        </button>
        @endif
        @if(!$review->staff_reply || $review->status === 'approved')
        <button type="button" class="btn btn-primary" onclick="closeViewModal(); openReplyModal({{ $review->id }})">
            {{ $review->staff_reply ? 'Update Reply' : 'Add Reply' }}
        </button>
        @endif
    </div>
</div>

<style>
.review-detail-card {
    padding: 24px;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.guest-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.guest-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 700;
    color: white;
}

.guest-name {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.guest-email {
    font-size: 14px;
    color: #6b7280;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 8px;
}

.star {
    font-size: 24px;
    color: #d1d5db;
}

.star.filled {
    color: #fbbf24;
}

.rating-number {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-left: 4px;
}

.review-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.meta-label {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.meta-value {
    font-size: 14px;
    color: #1f2937;
    font-weight: 500;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-approved {
    background: #d1fae5;
    color: #065f46;
}

.status-rejected {
    background: #fee2e2;
    color: #991b1b;
}

.review-comment-section {
    margin-bottom: 20px;
}

.review-comment-section strong {
    display: block;
    margin-bottom: 12px;
    font-size: 15px;
    color: #374151;
}

.review-text {
    color: #4b5563;
    line-height: 1.7;
    font-size: 15px;
    margin: 0;
    padding: 16px;
    background: white;
    border-left: 3px solid #3b82f6;
    border-radius: 6px;
}

.staff-reply-section {
    margin-bottom: 20px;
    padding: 16px;
    background: #ecfdf5;
    border-left: 3px solid #10b981;
    border-radius: 6px;
}

.reply-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.reply-header strong {
    color: #065f46;
    font-size: 15px;
}

.reply-date {
    font-size: 12px;
    color: #059669;
}

.reply-text {
    color: #047857;
    line-height: 1.6;
    margin: 0;
}

.no-reply-section {
    margin-bottom: 20px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 6px;
    text-align: center;
}

.no-reply-text {
    color: #9ca3af;
    font-style: italic;
    margin: 0;
}

.rejection-section {
    margin-bottom: 20px;
    padding: 16px;
    background: #fef2f2;
    border-left: 3px solid #ef4444;
    border-radius: 6px;
}

.rejection-section strong {
    display: block;
    margin-bottom: 8px;
    color: #991b1b;
}

.rejection-text {
    color: #dc2626;
    margin: 0 0 8px 0;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 10px 24px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.btn-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.text-muted {
    color: #6b7280;
    font-size: 12px;
}
</style>

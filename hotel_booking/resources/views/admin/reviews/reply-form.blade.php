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

    <div class="review-property">
        <strong>Property:</strong> {{ $review->property->name }}
    </div>

    <div class="review-comment">
        <strong>Review:</strong>
        <p>{{ $review->comment }}</p>
    </div>

    @if($review->staff_reply)
    <div class="existing-reply">
        <strong>Your Previous Reply:</strong>
        <p>{{ $review->staff_reply }}</p>
        <small class="text-muted">Replied {{ $review->replied_at->diffForHumans() }}</small>
    </div>
    @endif

    <div class="reply-form-section">
        <label for="replyText" class="form-label">
            <strong>{{ $review->staff_reply ? 'Update Your Reply' : 'Write Your Reply' }}:</strong>
        </label>
        <textarea 
            id="replyText" 
            class="form-textarea" 
            rows="5" 
            placeholder="Thank you for your review. We appreciate your feedback..."
        >{{ $review->staff_reply }}</textarea>
        <div class="form-help">Your reply will be visible to the guest and other users viewing this property.</div>
    </div>

    <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="closeReplyModal()">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="submitReply({{ $review->id }})">
            {{ $review->staff_reply ? 'Update Reply' : 'Post Reply' }}
        </button>
    </div>
</div>

<style>
.review-detail-card {
    padding: 20px;
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
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
    color: white;
}

.guest-name {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.guest-email {
    font-size: 13px;
    color: #6b7280;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 8px;
}

.star {
    font-size: 20px;
    color: #d1d5db;
}

.star.filled {
    color: #fbbf24;
}

.rating-number {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.review-property {
    margin-bottom: 15px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 6px;
    font-size: 14px;
    color: #374151;
}

.review-comment {
    margin-bottom: 20px;
}

.review-comment strong {
    display: block;
    margin-bottom: 8px;
    color: #374151;
}

.review-comment p {
    color: #4b5563;
    line-height: 1.6;
    margin: 0;
}

.existing-reply {
    margin-bottom: 20px;
    padding: 15px;
    background: #eff6ff;
    border-left: 3px solid #3b82f6;
    border-radius: 6px;
}

.existing-reply strong {
    display: block;
    margin-bottom: 8px;
    color: #1e40af;
}

.existing-reply p {
    color: #1e3a8a;
    margin: 0 0 8px 0;
}

.reply-form-section {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    color: #374151;
}

.form-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-family: inherit;
    font-size: 14px;
    resize: vertical;
    transition: border-color 0.2s;
}

.form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-help {
    font-size: 12px;
    color: #6b7280;
    margin-top: 6px;
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

.text-muted {
    color: #6b7280;
    font-size: 12px;
}
</style>

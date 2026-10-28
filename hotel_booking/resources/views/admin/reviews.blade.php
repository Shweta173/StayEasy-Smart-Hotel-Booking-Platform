@extends('admin.layout')

@section('title', 'Reviews')

@section('page-title', 'Reviews')

@section('content')
<style>
    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 700;
        color: #262626;
    }

    /* Statistics Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .stat-label {
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 700;
        color: #262626;
        line-height: 1;
    }

    .stat-subtitle {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 8px;
    }

    /* Filter Card */
    .filter-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr auto;
        gap: 16px;
        align-items: end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 8px;
    }

    .form-control {
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .btn-filter {
        padding: 10px 24px;
        background: #0071c2;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        height: 42px;
    }

    .btn-filter:hover {
        background: #00509e;
        transform: translateY(-1px);
    }

    /* Reviews Table */
    .reviews-card {
        background: white;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .reviews-table {
        width: 100%;
        border-collapse: collapse;
    }

    .reviews-table thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    .reviews-table th {
        padding: 16px 20px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .reviews-table td {
        padding: 20px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
        color: #262626;
    }

    .reviews-table tbody tr:last-child td {
        border-bottom: none;
    }

    .reviews-table tbody tr:hover {
        background: #fafbfc;
    }

    /* Guest Info */
    .guest-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .guest-name {
        font-weight: 600;
        color: #262626;
    }

    .guest-email {
        font-size: 12px;
        color: #6b7280;
    }

    /* Property Info */
    .property-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .property-name {
        font-weight: 600;
        color: #262626;
    }

    .booking-ref {
        font-size: 11px;
        color: #6b7280;
        font-family: 'Courier New', monospace;
    }

    /* Star Rating */
    .star-rating {
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .star {
        color: #fbbf24;
        font-size: 16px;
    }

    .star.empty {
        color: #e5e7eb;
    }

    .rating-number {
        font-size: 14px;
        font-weight: 700;
        color: #262626;
        margin-left: 6px;
    }

    /* Review Comment */
    .review-comment {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        color: #4b5563;
        line-height: 1.5;
    }

    /* Status Badges */
    .badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-pending {
        background: #fef3c7;
        color: #a16207;
    }

    .badge-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Action Buttons */
    .actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-approve {
        background: #d1fae5;
        color: #065f46;
    }

    .btn-approve:hover {
        background: #a7f3d0;
        transform: translateY(-1px);
    }

    .btn-reject {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-reject:hover {
        background: #fecaca;
        transform: translateY(-1px);
    }

    .btn-reply {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn-reply:hover {
        background: #bfdbfe;
        transform: translateY(-1px);
    }

    .btn-view {
        background: #f3f4f6;
        color: #4b5563;
    }

    .btn-view:hover {
        background: #e5e7eb;
        transform: translateY(-1px);
    }

    /* Reply Indicator */
    .reply-indicator {
        font-size: 11px;
        color: #10b981;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-top: 2px solid #e5e7eb;
        background: #f9fafb;
    }

    .pagination-info {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
    }

    .pagination a,
    .pagination span {
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .pagination a {
        background: white;
        color: #4b5563;
        border: 2px solid #e5e7eb;
    }

    .pagination a:hover {
        background: #f9fafb;
        border-color: #0071c2;
        color: #0071c2;
    }

    .pagination .active span {
        background: #0071c2;
        color: white;
        border: 2px solid #0071c2;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        width: 600px;
        max-width: 90%;
        max-height: 90vh;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Confirmation Modal */
    .confirm-modal-content {
        width: 450px;
        max-width: 90%;
        max-height: auto;
    }

    .confirm-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin: 0 auto 16px;
    }

    .confirm-icon.approve {
        background: #d1fae5;
        color: #065f46;
    }

    .confirm-icon.reject {
        background: #fee2e2;
        color: #991b1b;
    }

    .confirm-message {
        text-align: center;
        font-size: 16px;
        color: #4b5563;
        margin-bottom: 24px;
        line-height: 1.6;
    }

    .confirm-message strong {
        color: #262626;
        display: block;
        font-size: 18px;
        margin-bottom: 8px;
    }

    /* Toast Notification */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .toast {
        background: white;
        padding: 16px 20px;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 320px;
        max-width: 400px;
        animation: toastSlideIn 0.3s ease;
        border-left: 4px solid;
    }

    @keyframes toastSlideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes toastSlideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    .toast.hiding {
        animation: toastSlideOut 0.3s ease forwards;
    }

    .toast.success {
        border-left-color: #10b981;
    }

    .toast.error {
        border-left-color: #ef4444;
    }

    .toast.warning {
        border-left-color: #f59e0b;
    }

    .toast.info {
        border-left-color: #3b82f6;
    }

    .toast-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .toast.success .toast-icon {
        background: #d1fae5;
        color: #065f46;
    }

    .toast.error .toast-icon {
        background: #fee2e2;
        color: #991b1b;
    }

    .toast.warning .toast-icon {
        background: #fef3c7;
        color: #a16207;
    }

    .toast.info .toast-icon {
        background: #dbeafe;
        color: #1e40af;
    }

    .toast-content {
        flex: 1;
    }

    .toast-title {
        font-size: 14px;
        font-weight: 600;
        color: #262626;
        margin-bottom: 2px;
    }

    .toast-message {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.4;
    }

    .toast-close {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #f3f4f6;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        color: #6b7280;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .toast-close:hover {
        background: #e5e7eb;
        color: #262626;
    }

    .modal-header {
        padding: 24px;
        border-bottom: 2px solid #e5e7eb;
        background: #f9fafb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 700;
        color: #262626;
    }

    .modal-close {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        transition: all 0.2s ease;
    }

    .modal-close:hover {
        background: #fee2e2;
        border-color: #fecaca;
        color: #991b1b;
    }

    .modal-body {
        padding: 24px;
        overflow-y: auto;
        max-height: calc(90vh - 150px);
    }

    .review-detail {
        margin-bottom: 24px;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 16px;
    }

    .review-meta {
        flex: 1;
    }

    .review-full-comment {
        background: #f9fafb;
        padding: 16px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        line-height: 1.6;
        color: #4b5563;
        margin-bottom: 16px;
    }

    .staff-reply-section {
        background: #eff6ff;
        padding: 16px;
        border-radius: 8px;
        border: 2px solid #bfdbfe;
        margin-top: 16px;
    }

    .staff-reply-label {
        font-size: 12px;
        font-weight: 700;
        color: #1e40af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .staff-reply-text {
        color: #4b5563;
        line-height: 1.6;
    }

    .reply-form {
        margin-top: 24px;
    }

    .form-textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        resize: vertical;
        min-height: 120px;
    }

    .form-textarea:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .modal-footer {
        padding: 20px 24px;
        border-top: 2px solid #e5e7eb;
        background: #fafbfc;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .btn-cancel {
        padding: 10px 20px;
        background: white;
        color: #4b5563;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-cancel:hover {
        background: #f9fafb;
        border-color: #d1d5db;
    }

    .btn-submit {
        padding: 10px 24px;
        background: #0071c2;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-submit:hover {
        background: #00509e;
    }

    /* Empty State */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: #9ca3af;
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 16px;
    }

    .empty-text {
        font-size: 16px;
        font-weight: 600;
        color: #6b7280;
    }
</style>

<!-- Statistics Cards -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-label">Total Reviews</div>
        <div class="stat-value">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending</div>
        <div class="stat-value" style="color: #d97706;">{{ $stats['pending'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Approved</div>
        <div class="stat-value" style="color: #059669;">{{ $stats['approved'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Average Rating</div>
        <div class="stat-value" style="color: #0071c2;">{{ number_format($stats['avg_rating'], 1) }}</div>
        <div class="stat-subtitle">out of 5 stars</div>
    </div>
</div>

<!-- Filters -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.reviews') }}">
        <div class="filter-row">
            <div class="form-group">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Guest name, property, booking ref..." value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Rating</label>
                <select name="rating" class="form-control">
                    <option value="">All Ratings</option>
                    <option value="5" {{ request('rating') === '5' ? 'selected' : '' }}>5 Stars</option>
                    <option value="4" {{ request('rating') === '4' ? 'selected' : '' }}>4 Stars</option>
                    <option value="3" {{ request('rating') === '3' ? 'selected' : '' }}>3 Stars</option>
                    <option value="2" {{ request('rating') === '2' ? 'selected' : '' }}>2 Stars</option>
                    <option value="1" {{ request('rating') === '1' ? 'selected' : '' }}>1 Star</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Has Reply</label>
                <select name="has_reply" class="form-control">
                    <option value="">All</option>
                    <option value="yes" {{ request('has_reply') === 'yes' ? 'selected' : '' }}>Replied</option>
                    <option value="no" {{ request('has_reply') === 'no' ? 'selected' : '' }}>No Reply</option>
                </select>
            </div>
            <button type="submit" class="btn-filter">Filter</button>
        </div>
    </form>
</div>

<!-- Reviews Table -->
<div class="reviews-card">
    @if($reviews->count() > 0)
        <table class="reviews-table">
            <thead>
                <tr>
                    <th>Guest</th>
                    <th>Property</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviews as $review)
                <tr>
                    <td>
                        <div class="guest-info">
                            <div class="guest-name">{{ $review->guest?->name ?? $review->guest_name }}</div>
                            <div class="guest-email">{{ $review->guest?->email ?? $review->guest_email }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="property-info">
                            <div class="property-name">{{ $review->property->name }}</div>
                            @if($review->booking)
                                <div class="booking-ref">{{ $review->booking->booking_reference }}</div>
                            @else
                                <div class="booking-ref" style="color: #9ca3af;">No booking ref</div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="star-rating">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star {{ $i <= $review->rating ? '' : 'empty' }}">★</span>
                            @endfor
                            <span class="rating-number">{{ $review->rating }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="review-comment">{{ $review->comment }}</div>
                        @if($review->hasReply())
                            <div class="reply-indicator">✓ Staff Replied</div>
                        @endif
                    </td>
                    <td>
                        @if($review->isPending())
                            <span class="badge badge-pending">Pending</span>
                        @elseif($review->isApproved())
                            <span class="badge badge-approved">Approved</span>
                        @else
                            <span class="badge badge-rejected">Rejected</span>
                        @endif
                    </td>
                    <td>{{ $review->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="actions">
                            @if($review->isPending())
                                <button class="btn-action btn-approve" onclick="approveReview({{ $review->id }})">
                                    ✓ Approve
                                </button>
                                <button class="btn-action btn-reject" onclick="rejectReview({{ $review->id }})">
                                    ✗ Reject
                                </button>
                            @endif
                            @if($review->isApproved() && !$review->hasReply())
                                <button class="btn-action btn-reply" onclick="openReplyModal({{ $review->id }})">
                                    💬 Reply
                                </button>
                            @endif
                            <button class="btn-action btn-view" onclick="viewReview({{ $review->id }})">
                                👁 View
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Showing {{ $reviews->firstItem() ?? 0 }} to {{ $reviews->lastItem() ?? 0 }} of {{ $reviews->total() }} entries
            </div>
            {{ $reviews->links('vendor.pagination.custom') }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">📝</div>
            <div class="empty-text">No reviews found</div>
        </div>
    @endif
</div>

<!-- Reply Modal -->
<div class="modal" id="replyModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Reply to Review</h3>
            <button class="modal-close" onclick="closeReplyModal()">×</button>
        </div>
        <div class="modal-body" id="replyModalBody">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal" id="viewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Review Details</h3>
            <button class="modal-close" onclick="closeViewModal()">×</button>
        </div>
        <div class="modal-body" id="viewModalBody">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Approve Confirmation Modal -->
<div class="modal" id="approveModal">
    <div class="modal-content confirm-modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Approve Review</h3>
            <button class="modal-close" onclick="closeApproveModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon approve">✓</div>
            <div class="confirm-message">
                <strong>Approve this review?</strong>
                This review will be visible to all guests on the property page.
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeApproveModal()">Cancel</button>
            <button type="button" class="btn-submit" onclick="confirmApprove()">Yes, Approve</button>
        </div>
    </div>
</div>

<!-- Reject Confirmation Modal -->
<div class="modal" id="rejectModal">
    <div class="modal-content confirm-modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Reject Review</h3>
            <button class="modal-close" onclick="closeRejectModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon reject">✗</div>
            <div class="confirm-message">
                <strong>Reject this review?</strong>
                This review will be hidden and not shown to guests.
            </div>
            <div class="form-group" style="text-align: left; margin-top: 16px;">
                <label class="form-label">Reason (Optional)</label>
                <textarea id="rejectReason" class="form-textarea" placeholder="Enter reason for rejection..." style="min-height: 100px;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
            <button type="button" class="btn-submit" style="background: #dc2626;" onclick="confirmReject()">Yes, Reject</button>
        </div>
    </div>
</div>

<script>
    let currentReviewId = null;

    // Toast Notification System
    function showToast(message, type = 'success', title = '') {
        const toastContainer = document.getElementById('toastContainer');
        
        const icons = {
            success: '✓',
            error: '✗',
            warning: '⚠',
            info: 'ℹ'
        };

        const titles = {
            success: title || 'Success',
            error: title || 'Error',
            warning: title || 'Warning',
            info: title || 'Info'
        };

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">${icons[type]}</div>
            <div class="toast-content">
                <div class="toast-title">${titles[type]}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="closeToast(this)">×</button>
        `;

        toastContainer.appendChild(toast);

        // Auto remove after 4 seconds
        setTimeout(() => {
            closeToast(toast.querySelector('.toast-close'));
        }, 4000);
    }

    function closeToast(button) {
        const toast = button.closest('.toast');
        toast.classList.add('hiding');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }

    // Approve Review - Show Confirmation Modal
    function approveReview(reviewId) {
        currentReviewId = reviewId;
        document.getElementById('approveModal').classList.add('active');
    }

    // Close Approve Modal
    function closeApproveModal() {
        document.getElementById('approveModal').classList.remove('active');
        currentReviewId = null;
    }

    // Confirm Approve
    function confirmApprove() {
        if (!currentReviewId) return;

        fetch(`/admin/reviews/${currentReviewId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            closeApproveModal();
            if (data.msgtype === 'success' || data.success) {
                showToast(data.msg || data.message || 'Review has been approved and is now visible to guests', 'success', 'Review Approved');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.msg || data.message || 'Failed to approve review', 'error', 'Approval Failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            closeApproveModal();
            showToast('An error occurred while approving the review', 'error', 'Error');
        });
    }

    // Reject Review - Show Confirmation Modal
    function rejectReview(reviewId) {
        currentReviewId = reviewId;
        document.getElementById('rejectReason').value = '';
        document.getElementById('rejectModal').classList.add('active');
    }

    // Close Reject Modal
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.remove('active');
        currentReviewId = null;
    }

    // Confirm Reject
    function confirmReject() {
        if (!currentReviewId) return;

        const reason = document.getElementById('rejectReason').value.trim();

        fetch(`/admin/reviews/${currentReviewId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ reason }),
        })
        .then(response => response.json())
        .then(data => {
            closeRejectModal();
            if (data.msgtype === 'success' || data.success) {
                showToast(data.msg || data.message || 'Review has been rejected and will not be shown to guests', 'success', 'Review Rejected');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.msg || data.message || 'Failed to reject review', 'error', 'Rejection Failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            closeRejectModal();
            showToast('An error occurred while rejecting the review', 'error', 'Error');
        });
    }

    // Open Reply Modal
    function openReplyModal(reviewId) {
        fetch(`/admin/reviews/${reviewId}/reply-form`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('replyModalBody').innerHTML = html;
                document.getElementById('replyModal').classList.add('active');
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to load reply form', 'error', 'Error');
            });
    }

    // Close Reply Modal
    function closeReplyModal() {
        document.getElementById('replyModal').classList.remove('active');
    }

    // Submit Reply
    function submitReply(reviewId) {
        const reply = document.getElementById('replyText').value.trim();

        if (!reply) {
            showToast('Please enter a reply message', 'warning', 'Reply Required');
            return;
        }

        fetch(`/admin/reviews/${reviewId}/reply`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ reply }),
        })
        .then(response => response.json())
        .then(data => {
            closeReplyModal();
            if (data.msgtype === 'success' || data.success) {
                showToast(data.msg || data.message || 'Your reply has been posted and is visible to guests', 'success', 'Reply Posted');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.msg || data.message || 'Failed to submit reply', 'error', 'Reply Failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            closeReplyModal();
            showToast('An error occurred while submitting the reply', 'error', 'Error');
        });
    }

    // View Review
    function viewReview(reviewId) {
        fetch(`/admin/reviews/${reviewId}/view`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('viewModalBody').innerHTML = html;
                document.getElementById('viewModal').classList.add('active');
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to load review details', 'error', 'Error');
            });
    }

    // Close View Modal
    function closeViewModal() {
        document.getElementById('viewModal').classList.remove('active');
    }

    // Close modals on overlay click
    document.getElementById('replyModal').addEventListener('click', function(e) {
        if (e.target === this) closeReplyModal();
    });

    document.getElementById('viewModal').addEventListener('click', function(e) {
        if (e.target === this) closeViewModal();
    });

    document.getElementById('approveModal').addEventListener('click', function(e) {
        if (e.target === this) closeApproveModal();
    });

    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) closeRejectModal();
    });

    // Close modals on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReplyModal();
            closeViewModal();
            closeApproveModal();
            closeRejectModal();
        }
    });
</script>

@endsection

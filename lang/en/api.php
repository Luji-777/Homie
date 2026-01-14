<?php

return [
  'test' => 'This is a test message in English',

  'account_created_success' => 'the account created successfully, Please verify your phone number.',
  'invalid_otp'             => 'the code isnot true',
  'pending_admin_approval'  => 'waiting the admin to approve the account.',
  'otp_resent_success'      => 'OTP resent successfully.',
  'invalid_login'           => 'Invalid login details',
  'login_success'           => 'Login successful',
  'logout_success'          => 'Logout successful',

  'apartment_created'    => 'Apartment created successfully. Waiting for admin approval.',
  'apartment_updated'    => 'Apartment updated successfully.',
  'apartment_deleted'    => 'Apartment deleted successfully.',
  'apartments_retrieved' => 'Apartments retrieved successfully.',
  'unauthorized'         => 'Unauthorized',

  'type_room'   => 'Room',
  'type_studio' => 'Studio',
  'type_house'  => 'House',
  'type_villa'  => 'Villa',

  'rent_day'   => 'Daily',
  'rent_month' => 'Monthly',







  // Booking statuses
  'pending_owner_approval' => 'Pending Owner Approval',
  'owner_approved' => 'Approved',
  'owner_rejected' => 'Rejected',
  'completed' => 'Completed',
  'cancelled' => 'Cancelled',
  'tenant_cancel_request' => 'Tenant Cancellation Request',
  'owner_cancel_accepted' => 'Cancellation Accepted',
  'owner_cancel_rejected' => 'Cancellation Rejected',
  'tenant_modify_request' => 'Tenant Modification Request',
  'owner_modify_accepted' => 'Modification Accepted',
  'owner_modify_rejected' => 'Modification Rejected',

  // General messages
  'unauthorized_action' => 'You are not authorized to perform this action',
  'insufficient_balance' => 'Insufficient balance to complete this booking',
  'pending_booking' => 'Cannot change status of a non-pending booking',
  'cancellation_not_allowed' => 'Cannot cancel a non-confirmed booking',
  'modification_not_allowed' => 'Cannot modify a non-confirmed booking',
  'date_overlap' => 'The new dates overlap with another confirmed booking',
  'invalid_date_format' => 'Invalid date format',
  'modification_text_error' => 'Unable to read dates from the request',
  'insufficient_additional_balance' => 'Tenant balance is insufficient to cover the additional required amount',
  'insufficient_deduction_balance' => 'Owner balance is insufficient to cover the required deduction',
  'booking_request_sent' => 'Booking request submitted successfully, awaiting owner approval',
  'cancellation_request_sent' => 'Cancellation request sent successfully, awaiting owner approval',
  'modification_request_sent' => 'Modification request sent successfully, awaiting owner approval',
  'cancellation_success' => 'Booking cancelled successfully',
  'modification_success' => 'Modification request approved and applied successfully',
  'failed_to_read_dates' => 'Failed to read the dates from the request',
  'invalid_date_format' => 'Invalid date format',
  'insufficient_tenant_balance' => 'Tenant balance is insufficient to cover the required price increase',
  'insufficient_owner_balance' => 'Owner balance is insufficient to cover the required price deduction',

  'pending'        => 'Pending',
  'owner_approved' => 'Approved by owner',
  'owner_rejected' => 'Rejected by owner',
  'cancelled'      => 'Cancelled',
  'completed'      => 'Completed',

  'overlapping_dates' => 'The new dates overlap with another confirmed booking',
  'not_authorized_to_manage' => 'You are not authorized to manage this booking',
  'cannot_change_non_pending' => 'Cannot change status of a non-pending booking',
  'approved_and_transferred' => 'Approved and amount transferred successfully',
  'rejected_and_refunded' => 'Rejected and amount refunded successfully',
  'not_authorized_to_cancel' => 'You are not authorized to cancel this booking',
  'not_authorized_to_modify' => 'You are not authorized to modify this booking',
  'cannot_cancel_unconfirmed' => 'Cannot cancel a non-confirmed booking',
  'cannot_modify_unconfirmed' => 'Cannot modify a non-confirmed booking',
  'not_authorized_to_handle_cancellation' => 'You are not authorized to handle cancellation requests for this booking',
  'not_authorized_to_handle_modification' => 'You are not authorized to handle modification requests for this booking',
  'no_cancellation_request' => 'No cancellation request to process',
  'no_modification_request' => 'No modification request to process',
  'cancellation_rejected' => 'Cancellation request rejected',
  'cancellation_accepted' => 'Cancellation request accepted',
  'modification_rejected' => 'Modification request rejected',
  'modification_accepted' => 'Modification request accepted',



  'invalid_booking_apartment' => 'The booking does not match the requested apartment',
  'review_not_allowed' => 'You cannot review a booking before it is completed',
  'review_already_exists' => 'You have already reviewed this apartment',
  'review_created_success' => 'Review created successfully',
  'cannot_delete_review_unauthorized' => 'You cannot delete a review that does not belong to you',
  'review_deleted_success' => 'Review deleted successfully',


  'apartment_already_favorite' => 'Apartment is already in favorites',
  'apartment_added_to_favorites_successfully' => 'Apartment added to favorites successfully',
  'apartment_non_favorite' => 'Apartment is not in favorites',
  'apartment_removed_from_favorites_successfully' => 'Apartment removed from favorites successfully',



  'profile_updated_successfully' => 'Profile updated successfully',
  ];

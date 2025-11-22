Movie Website Monetization System
===============================

This system tracks user views and watch time to calculate earnings based on the following formula:
1 view + 5 minutes of watch time = 2,000 Rwandan Francs (RWF)

System Components:
------------------
1. Database Tables:
   - user_views: Tracks user-specific views and watch time
   - withdrawals: Manages withdrawal requests

2. Admin Dashboard:
   - File: admin/monetization.php
   - Features:
     * View total earnings, views, and watch time
     * See earnings breakdown
     * Manage withdrawal requests (approve/reject)

3. User Dashboard:
   - Users do not have access to the monetization system
   - Only administrators can view and manage monetization features

4. Watch Time Tracking:
   - Modified: watch.php
   - Added JavaScript to track watch time
   - Updates server every 30 seconds
   - Handles video play/pause/end events

5. Backend Processing:
   - File: update_watch_time.php
   - Handles watch time updates from client-side JavaScript
   - Only accessible to authenticated users

Setup Instructions:
-------------------
1. Run setup_db.php to create the required database tables
2. Ensure users are logged in to track their views and watch time
3. Access the admin monetization dashboard from the admin panel

Earnings Calculation:
---------------------
- Views contribute 50% of earnings (1,000 RWF per view)
- Watch time contributes 50% of earnings (1,000 RWF per 5 minutes)
- Total earnings = (views × 1000) + (watch_time_in_seconds ÷ 300 × 1000)

Withdrawal Process:
-------------------
1. Users request withdrawals from their earnings dashboard
2. Admins review and approve/reject requests from the monetization dashboard
3. Approved withdrawals are processed automatically via MoMo API
4. Admins can check the status of processed withdrawals

MoMo API Integration:
--------------------
- File: MoMoAPI.php
- Configuration: momo_config.php
- Features:
  * Automatic processing of approved withdrawals via Mobile Money
  * Status checking for processed transactions
  * Support for MTN Mobile Money and Airtel Money
  * Secure handling of API credentials
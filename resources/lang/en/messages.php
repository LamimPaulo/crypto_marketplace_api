<?php

return [
    '2fa' => [
        'activated' => 'Google 2FA Activated.',
        'already_activated' => 'Google 2FA already activated.',
        'deactivated' => 'Google 2FA Deactivated.',
        'invalid_code' => 'Invalid or Expired Code.',
        'invalid_secret' => 'The secret is invalid.',
        'not_activated' => 'Google 2FA not activated.',
    ],
    'auth' => [
        'access_denied' => 'Access denied.',
        'already_have_active_key' => 'You already have an active key.',
        'email_not_found' => 'Email not found in our database.',
        'email_sent' => 'Email successfully sent, check your inbox.',
        'id_could_not_be_verified' => 'Could not verify the cpf reported.',
        'invalid_client' => 'Invalid Client.',
        'invalid_code' => 'Invalid Code.',
        'invalid_key' => 'Invalid Key.',
        'invalid_pin' => 'Invalid PIN.',
        'invalid_token' => 'Invalid Token',
        'password_change_success' => 'Password updated successfully.',
        'pin_updated' => 'PIN updated successfuly.',
        'telephone_update_error' => 'Error updating number!',
        'telephone_number_verified' => 'Phone Verified Successfully (:number)',
        'telephone_verified' => 'Phone Verified Successfully!',
        'token_email_invalid' => 'Token or incorrect mail.',
        'unauthorized_ip' => 'Unauthorized IP [:address].',
        'you_could_not_updated_your_id' => 'You can not update your ID again.',
    ],
    'account' => [
        'updated' => 'Account updated successfully.',
        'created' => 'Account created successfully.',
        'deleted' => 'Account deleted successfully.',
        'not_found' => 'No account found.',

        'beneficiary_not_found' => 'Beneficiary not found.',
        'beneficiary_already_registered' => 'The Beneficiary is already registered.',
        'email_not_exists' => 'The informed email does not exist.',
        'must_register_email_recepient_first' => 'You must register the email as a beneficiary before making the transaction.',
        'must_confirm_account_to_require_transferences' => 'You must verify your account to request transfers between accounts.',
        'you_could_not_be_recipient' => 'You can not register as a beneficiary.',
    ],
    'coin' => [
        'inactive' => 'The requested coin is inactive.',
        'incompatible' => 'The requested currency does not match your profile.',
        'must_be_distinct' => 'The coins must be different.',
        'can_not_be_converted' => 'The requested coins can not be converted.',
        'not_compatible_with_investment' => 'The type of currency is incompatible with the type of investment.',
    ],
    'deposit' => [
        'sent' => 'Deposit successfully sent.',
        'done' => 'Deposit confirmed.',
        'rejected' => 'Deposit rejected - :reason.',
        'already_pending' => 'Unable to complete request, a pending deposit exists.',
        'value_not_reached_min' => 'The deposit amount has not reached the minimum.',
    ],
    'documents' => [
        'accept' => 'Documents Accepted.',
        'pending' => 'Documents not sent or pending approval.',
        'reject' => 'Documents rejected - :reason',
        'sent_error' => 'Error sending document!',
        'file_no_longer_available' => 'File is not available for this user.',
    ],
    'gateway' => [
        'address_generated' => 'Address generated successfully.',
        'must_create_api_key' => 'To request payments you need to create an Api Key.',
        'not_elegible' => 'You do not have permission to use the Payment Gateway.',
        'payment_expired' => 'Payment has already been processed or has expired, it is not possible to complete the operation.',
        'payment_time_expired' => 'Payment can not be generated, time is up.',
        'payment_could_not_be_updated' => 'Payment can not be modified.',
        'payment_not_found' => 'Payment not found. (TX does not exist)',
        'submission_could_not_be_processed' => 'The Submission can not be processed, the gateway payment and the sent currency are not compatible.',
    ],
    'general' => [
        'invalid_data' => 'Invalid Data.',
        'invalid_id' => 'Invalid ID.',
        'invalid_operation_type' => 'Incorrect operation type.',
        'level_up' => 'Level up!',
        'status_updated' => 'Status Updated.',
        'success' => 'Success!',
    ],
    'products' => [
        'arbitrage' => 'Arbitrage',
        'crypto_assets' => 'Crypto Assets',
        'error_creating_investment' => 'Error creating investment.',
        'fund_not_acquired' => 'You do not have quotas in the Index Fund selected.',
        'hiring_success' => 'Hiring successful.',
        'index_fund' => 'Index Funds',
        'index_fund_hiring_success' => 'Index Fund purchased successfully!',
        'index_fund_sold_success' => 'Index Fund sold successfully!',
        'insuficient_investment_balance' => 'Insufficient investment balance to make withdrawals.',
        'insuficient_profit' => 'Insufficient profit. The investment amount is greater than your available profit.',
        'insuficient_quotes' => 'Insufficient quotas to carry out the operation.',
        'investment_success' => 'Investment made successfully.',
        'invalid_contract_method' => 'It is not possible to contract in the chosen way.',
        'minimum_purchase_value_not_reached' => 'The minimum purchase value was not reached. (:amount :abbr)',
        'mining' => 'Mining',
        'not_allowed_sell_by_fiat' => 'Your profile does not allow the sale of products by Fiat Currency.',
        'not_allowed_buy_with_fiat' => 'Your profile does not allow the purchase of products with Fiat Currency.',
        'ths_sold_out' => 'It is not possible to hire the requested quantity. We currently have only :remaining Th/s available. Try to hire a number equal to or less than this.',
    ],
    'transaction' => [
        'amount_must_be_grater_than_zero' => 'Amount must be greater than 0.',
        'conversion_success' => 'Conversion successful!',
        'crypto_sent' => 'Crypto Sent.',
        'crypto_received' => 'Crypto Received.',
        'invalid' => 'Invalid transaction.',
        'invalid_value_sent' => 'The value you submitted is invalid.',
        'invalid_value_request' => 'The requested value is invalid.',
        'not_found' => 'Transaction not found.',
        'order_sent' => 'Order submitted successfully!',
        'reversed' => 'Transação estornada - :reason',
        'sent_blockchain' => 'Transação enviada para a blockchain.',
        'sent_success' => 'Successful Sending!',
        'value_below_the_minimum' => 'Value below the minimum. (:amount)',
        'value_exceeds_balance' => 'Value of the transaction above the balance.',
        'value_exceeds_day_limits' => 'Transaction value exceeds daily limits.',
        'value_exceeds_level_limits' => 'Transaction value exceeds daily limit according to your level.',
        'value_must_be_greater_than_zero' => 'Value must be greater than 0.',
    ],
    'wallet' => [
        'inactive' => 'The requested wallet is inactive.',
        'invalid_for_coin' => 'There is no valid wallet for the requested currency.',
        'invalid_wallet' => 'There is no valid wallet for the requisition.',
        'insuficient_balance' => 'Insufficient funds.',
        'insuficient_balances' => 'Insufficient funds.',
    ],
    'withdrawal' => [
        'already_pending' => 'Unable to complete request, a pending withdrawal request already exists.',
        'canceled_by_user' => 'Withdrawal Canceled by User.',
        'canceled' => 'Cashout Successfully canceled.',
        'done' => 'Withdrawal Done.',
        'processing' => 'Withdrawal Processing.',
        'success' => 'Withdrawal Success.',
        'requested' => 'Withdrawal Requested.',
        'reversed' => 'Withdrawal Reversed.',
        'must_confirm_account_to_require_draft' => 'You must verify your account to request withdrawals.',
    ],
];
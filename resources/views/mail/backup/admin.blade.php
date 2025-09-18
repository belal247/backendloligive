<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject }}</title>
</head>
<body>
    <p>Dear CardNest Admin Team,</p>
    
    <p>This is an automated notification to inform you that <strong>new customer/business applications have been submitted and are pending review</strong>.</p>

    <p>Please log into the <strong>CardNest Admin Dashboard</strong> to review and take the necessary action.</p>

    <p><strong>Details of Pending Submissions</strong>:</p>

    <p>
        <ul>
            <li>
                <strong>Total Applications Pending:</strong> {{ $admin['total_application_pending'] }}
            </li>
            <li>
                <strong>Submission Date(s):</strong> {{ $admin['submission_dates'] }}
            </li>
            <li>
                <strong>Customer/Business Name(s):</strong> {{ $admin['customer_business_names'] }}
            </li>
            <li>
                <strong>Status:</strong> Awaiting admin review and approval
            </li>
        </ul>
    </p>

    <p><strong>Next Steps Required:</strong></p>

     <p>
        <ol>
            <li>
                Verify all submitted information and required documentation.
            </li>
            <li>
                Conduct due diligence checks in line with <strong>CardNest compliance policies (KYC/AML requirements).</strong>
            </li>
            <li>
                Approve, reject, or request additional information from the applicant.
            </li>
            <li>
                Update the application status in the system accordingly.
            </li>
        </ol>
    </p>

    <p>
        Please ensure reviews are completed within the <strong>standard 24-hour window</strong> to maintain our commitment to prompt onboarding for new customers and business partners.
    </p>

    <p>
        If you encounter any technical issues or discrepancies, kindly escalate to <strong>support@cardnest.io</strong> immediately.
    </p>

    <p>
        Thank you for your attention and commitment to maintaining the security and credibility of CardNest’s onboarding process.    
    </p>

    <p>
        Best regards,<br>
        <strong>CardNest System Notifications</strong><br>
        <a href="https://www.cardnest.io" target="_blank">www.cardnest.io</a>
    </p>
</body>
</html>
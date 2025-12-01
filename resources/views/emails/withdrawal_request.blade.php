<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Withdrawal Request</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="text-align: center; padding-bottom: 20px;">
                            <h2 style="color: #333;">New Withdrawal Request</h2>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" cellpadding="5" cellspacing="0">
                                <tr>
                                    <td style="font-weight: bold; color: #555;">Org ID:</td>
                                    <td>{{ $withdrawal->org_id }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #555;">Amount:</td>
                                    <td>${{ number_format($withdrawal->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #555;">Is Zelle:</td>
                                    <td>{{ $withdrawal->isZelle ? 'Yes' : 'No' }}</td>
                                </tr>

                                @if($withdrawal->isZelle)
                                    <tr>
                                        <td style="font-weight: bold; color: #555;">Zelle Name:</td>
                                        <td>{{ $withdrawal->zelle_name }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold; color: #555;">Zelle Email:</td>
                                        <td>{{ $withdrawal->zelle_email }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold; color: #555;">Zelle Phone:</td>
                                        <td>{{ $withdrawal->zelle_phone }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td style="font-weight: bold; color: #555;">Account Name:</td>
                                        <td>{{ $withdrawal->account_holder_name }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold; color: #555;">Account Number:</td>
                                        <td>{{ $withdrawal->account_no }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold; color: #555;">Bank Name:</td>
                                        <td>{{ $withdrawal->bank_name }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold; color: #555;">IBAN:</td>
                                        <td>{{ $withdrawal->iban }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold; color: #555;">Branch:</td>
                                        <td>{{ $withdrawal->branch_address }}</td>
                                    </tr>
                                @endif

                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 20px; text-align: center;">
                            <p style="color: #888; font-size: 12px;">This is an automated notification from {{ config('app.name') }}.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Registration Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            color: #333333;
        }
        a {
            color: #1a73e8;
            text-decoration: none;
        }
        .content {
            max-width: 600px;
            margin: auto;
        }
        h2 {
            color: #2c3e50;
        }
        ul {
            padding-left: 20px;
        }
        ul li {
            margin-bottom: 10px;
        }
        .signature {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    @php
    $forum_link = 'https://prdp-online.com/';
    @endphp
    <div class="content">
        <h2>REGISTRATION CONFIRMATION EMAIL</h2>

        <p>Dear {{ $user->firstname }} {{ $user->lastname }},</p>

        <p>
            Thank you for registering for the National Agri-Fishery Investment Forum, to be held on September 16–18, 2025. Venue details, the specific program, and other advisories will be sent in the coming days to your email. Please also check the Forum website regularly for updates.
        </p>

        <p>
            Forum Website: <a href="{{ $forum_link }}">{{ $forum_link }}</a>
        </p>

        <p>Below are important reminders for your participation:</p>

        <ul>
            <li><strong>Arrival and Registration:</strong> Please arrive at least one (1) hour before the start of the day’s activities for registration.</li>
            <li><strong>ID Verification:</strong> Present the generated participant ID attached to this email (digital or printed). This will be scanned upon entry as official verification.</li>
            <li><strong>Entry:</strong> Only confirmed and registered participants will be allowed entry to the event.</li>
            <li><strong>Governors:</strong> Expected to attend the high-level sessions and plenary discussions on September 18 and are required to join the Fellowship Dinner on September 17 at 6:00 PM as part of the official program.</li>
            <li><strong>Other PLGU participants:</strong> The SP Committee Chair on Agriculture is highly encouraged to attend the full duration of the Forum, along with other official PLGU representatives.</li>
            <li><strong>Accommodation and Meals:</strong> Officially registered participants will be provided accommodation and meals. Travel arrangements and transportation expenses remain the responsibility of the participating LGUs.</li>
            <li><strong>Accompanying Personnel:</strong> Due to limited allocations, accommodation and meals cannot be extended to non-registered companions (e.g., drivers, security escorts). LGUs are requested to manage these arrangements separately.</li>
            <li><strong>Preparation for Sessions:</strong> Each provincial delegation is advised to bring:
                <ul>
                    <li>At least one (1) laptop for use during technical workshops</li>
                    <li>Mobile phones or tablets for interactive tools (with sufficient data load as backup)</li>
                    <li>Electronic copies of relevant provincial plans and budgets (AIPs, PCIPs, or related investment documents)</li>
                </ul>
            </li>
        </ul>

        <p>Thank you and we look forward to your active participation in this important Forum.</p>

        <div class="signature">
            <p>Sincerely,</p>
            <p>Secretariat<br />National Agri-Fishery Investment Forum</p>
        </div>
    </div>
</body>
</html>

@php
    $baseWidth = 310;
    $baseHeight = 490;
    $scale = 3245 / $baseWidth;

    $firstNameWords = explode(' ', strtoupper($user->firstname));
    $firstName = count($firstNameWords) > 1 ? $firstNameWords[0] . ' ' . $firstNameWords[1] : $firstNameWords[0];
    $lastName = strtoupper($user->lastname);

    // Dynamic font sizing for name
    $firstFontSize = 28 * $scale;
    $lastFontSize = 18 * $scale;
    $maxWidth = (250 - 8) * $scale;

    if (strlen($firstName) > 8) {
        $approxCharWidth = 12 * $scale;
        $nameWidth = strlen($firstName) * $approxCharWidth;
        while ($nameWidth > $maxWidth && $firstFontSize > 10 * $scale) {
            $firstFontSize--;
            $approxCharWidth = 0.5 * $firstFontSize;
            $nameWidth = strlen($firstName) * $approxCharWidth;
        }
    }

    $approxCharWidth = 9 * $scale;
    $nameWidth = strlen($lastName) * $approxCharWidth;
    while ($nameWidth > $maxWidth && $lastFontSize > 8 * $scale) {
        $lastFontSize--;
        $approxCharWidth = 0.5 * $lastFontSize;
        $nameWidth = strlen($lastName) * $approxCharWidth;
    }

    // Office text mapping
    $officeText = strtoupper($user->office);
    $officeMap = [
        'PHILIPPINE COUNCIL FOR AGRICULTURE AND FISHERIES (PCAF)' => 'PCAF',
        'COMMODITY EXPERTS (RESOURCE PERSONS)' => 'RESOURCE PERSONS',
        'REGIONAL AGRICULTURAL AND FISHERY COUNCIL (RAFC)' => 'RAFC',
        'BUREAU OF AGRICULTURAL AND FISHERIES ENGINEERING (BAFE)' => 'BAFE',
        'TANGGOL KALIKASAN (FACILITATORS)' => 'FACILITATORS',
        'BUREAU OF FISHERIES AND AQUATIC RESOURCES (BFAR)' => 'BFAR',
        'PROJECT DEVELOPMENT SERVICE (PDS)' => 'PDS',
    ];
    $displayOffice = strtoupper($officeMap[$officeText] ?? $user->office);

    // Dynamic font size for office text
    $maxChars = 27;
    $minChars = 3;
    $maxFontSizeOffice = 250;
    $minFontSizeOffice = 145;
    $charCount = strlen($displayOffice);

    if ($charCount <= $minChars) {
        $fontSizeOffice = $maxFontSizeOffice;
    } elseif ($charCount >= $maxChars) {
        $fontSizeOffice = $minFontSizeOffice;
    } else {
        $ratio = ($charCount - $minChars) / ($maxChars - $minChars);
        $fontSizeOffice = $maxFontSizeOffice - $ratio * ($maxFontSizeOffice - $minFontSizeOffice);
    }

    $adjust = -0.5; // global fine-tune
    $fontSizeOffice += $adjust;

    // QR code size
    $qrSize = 250 * $scale;
    $qrMargin = 2;

    // ID number
    $idTop = 380 * $scale;
    $idLeft = 220 * $scale;
    $idFontSize = 12 * $scale;
@endphp

<div style="width:100vw; height:100vh; display:flex; justify-content:center; align-items:center; background:#f0f0f0;">
    <div
        style="width:{{ $baseWidth * $scale }}px; height:{{ $baseHeight * $scale }}px; position:relative; overflow:hidden; background:#fff; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.2);">

        <!-- Background -->
        <img src="{{ $bgSrc }}" alt="ID Background"
            style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:0;">

        <div
            style="position:relative; z-index:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-start; padding:{{ 20 * $scale }}px;">

            <!-- Profile Image -->
            <div style="margin-top:{{ 32 * $scale }}px;">
                <img src="{{ $userImageSrc }}" alt="Profile Picture"
                    style="display:block; margin:0 auto; width:{{ 160 * $scale }}px; height:{{ 160 * $scale }}px; object-fit:cover; border:{{ 2 * $scale }}px solid rgba(255,255,255,0.8); border-radius:{{ 80 * $scale }}px;">
            </div>

            <!-- Names -->
            <div style="display:flex; flex-direction:column; align-items:center; margin-top:45px;">
                <div
                    style="text-align:center; margin-top:0px; white-space:nowrap; overflow:hidden; padding-left:{{ 4 * $scale }}px; padding-right:{{ 4 * $scale }}px;">
                    <div
                        style="font-family:'Arial Black', Arial, sans-serif; font-size:{{ $firstFontSize }}px; font-weight:900; color:#2f4482; line-height:1.1;">
                        {{ $firstName }}
                    </div>
                    <div
                        style="font-family:Arial, sans-serif; font-size:{{ $lastFontSize }}px; font-style:italic; color:#2f4482; line-height:1.1; font-weight:600;">
                        {{ $lastName }}
                    </div>
                </div>

                <!-- Office Text Container -->
                <div style="width:100%; display:flex; justify-content:center; align-items:center; flex:1;">
                    <div
                        style="text-align:center; font-family:Arial, sans-serif;
                font-size:{{ $fontSizeOffice }}px;
                color:#d3e6e9;
                font-weight:800;
                line-height:1;">
                        {{ $displayOffice }}
                    </div>
                </div>

            </div>

            <!-- QR and ID -->
            <div
                style="position:relative; margin-top:{{ 8 * $scale }}px; height:{{ 130 * $scale }}px; width:100%;">
                <div
                    style="width:{{ 120 * $scale }}px; height:{{ 120 * $scale }}px; margin:0 auto; display:flex; justify-content:center; align-items:center;">
                    {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size($qrSize)->margin($qrMargin)->generate('https://prdp-pso-nl-auction.da.gov.ph/investment-forum-user-verification/' . $user->id) !!}
                </div>

                <div
                    style="position:absolute; top:920px; left:2100px; font-family:'NeulisSansBold', Arial, sans-serif; font-size:500px; color:#2f4482; line-height:1.2;">
                    <div style="font-family:Arial, sans-serif; font-size:{{ $idFontSize }}px; font-weight:700;">ID
                        No:</div>
                    <div style="font-family:Arial, sans-serif; font-size:{{ $idFontSize }}px; font-weight:800;">
                        {{ $user->login_code }}</div>
                </div>
            </div>

        </div>
    </div>
</div>

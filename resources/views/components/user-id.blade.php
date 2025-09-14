@php
    // Base dimensions of your original design
    $baseWidth = 300;
    $baseHeight = 490;

    // Target dimensions for Browsershot
    $targetWidth = 330;
    $targetHeight = 515;

    // Scale factor to fit card into target size while keeping aspect ratio
    $scaleX = $targetWidth / $baseWidth;
    $scaleY = $targetHeight / $baseHeight;
    $scale = min($scaleX, $scaleY);

    // Split names
    $firstNameWords = explode(' ', strtoupper($user->firstname));
    $firstName = count($firstNameWords) > 1 ? $firstNameWords[0] . ' ' . $firstNameWords[1] : $firstNameWords[0];
    $lastName = strtoupper($user->lastname);

    // Font sizing for first and last name
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

    // QR code & ID
    $qrSize = 40 * $scale;
    $qrMargin = 2;
    $idFontSize = 10 * $scale;

    // Profile image
    $profileImgSize = 160 * $scale;
    $profileImgBorder = 2 * $scale;

    // Padding & margins
    $padding = 20 * $scale;
    $marginTopProfile = 32 * $scale;
    $marginTopNames = 5 * $scale;
    $bottomSection = 50 * $scale;

    // Office & designation
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

    $designation = strtoupper($user->designation);
@endphp

<div
    style="width:{{ $baseWidth * $scale }}px; height:{{ $baseHeight * $scale }}px; position:relative; overflow:hidden; background:#fff; border-radius:{{ 10 * $scale }}px; box-shadow:0 0 {{ 20 * $scale }}px rgba(0,0,0,0.2); font-family:Arial, sans-serif;">

    <!-- Background -->
    <img src="{{ $bgSrc }}" alt="ID Background"
        style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:0; object-fit:cover;">

    <!-- Main Content -->
    <div
        style="position:relative; z-index:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-start; padding:{{ $padding }}px;">

        <!-- Profile Image -->
        <div style="margin-top:{{ $marginTopProfile }}px;">
            <img src="{{ $userImageSrc }}"
                style="width:{{ $profileImgSize }}px; height:{{ $profileImgSize }}px; object-fit:cover; border:{{ $profileImgBorder }}px solid rgba(255,255,255,0.8); border-radius:{{ $profileImgSize / 2 }}px;">
        </div>

        <!-- Names -->
        <div
            style="display:flex; flex-direction:column; align-items:center; margin-top:{{ $marginTopNames }}px; text-align:center;">
            <div
                style="font-family:'Arial Black', Arial, sans-serif; font-size:{{ $firstFontSize }}px; font-weight:900; color:#2f4482; line-height:1.1;">
                {{ $firstName }}
            </div>
            <div
                style="font-family:Arial, sans-serif; font-size:{{ $lastFontSize }}px; font-style:italic; font-weight:600; color:#2f4482; line-height:1.1;">
                {{ $lastName }}
            </div>
        </div>

        <!-- Office / Designation -->
        <div style="margin-top:{{ 20 * $scale }}px; text-align:center; width:100%;">
            <div style="font-size:{{ 14.5 * $scale }}px; font-weight:900; color:#2f4482; line-height:1;">
                {{ $designation }}
            </div>
            <div
                style="font-size:{{ 8 * $scale }}px; font-style:italic; color:#666; margin-top:{{ $scale }}px; line-height:1;">
                @if (strtoupper($user->institution) === 'PROVINCIAL LOCAL GOVERNMENT UNITS')
                    PROVINCIAL LGU - {{ strtoupper($user->province->abbr) }}, {{ strtoupper($user->region->abbr) }}
                @elseif (strtoupper($user->institution) === 'DA REGIONAL FIELD OFFICE')
                    DA RFO
                @elseif (strtoupper($user->institution) === 'DA CENTRAL OFFICE')
                    DA CO
                @elseif (strtoupper($user->institution) === 'OTHER INSTITUTIONS')
                    @if (strtoupper($user->office) === 'COMMODITY EXPERTS (RESOURCE PERSONS)')
                        COMMODITY EXPERTS
                    @elseif (strtoupper($user->office) === 'TANGGOL KALIKASAN (FACILITATORS)')
                        TANGGOL KALIKASAN
                    @else
                        {{ strtoupper($user->office) }}
                    @endif
                @else
                    {{ strtoupper($user->institution) }}
                @endif

            </div>
        </div>

    </div>

    <!-- QR & ID at bottom -->
    <div
        style="position:absolute; bottom:{{ $bottomSection }}px; left:50%; transform:translateX(-50%); display:flex; align-items:center; gap:{{ 20 * $scale }}px; z-index:2;">

        <!-- QR Code -->
        <div
            style="width:{{ $qrSize }}px; height:{{ $qrSize }}px; display:flex; justify-content:center; align-items:center;">
            {!! QrCode::size($qrSize)->margin($qrMargin)->generate('https://prdp-pso-nl-auction.da.gov.ph/investment-forum-user-verification/' . $user->id) !!}
        </div>

        <!-- ID Number -->
        <div style="text-align:left; line-height:1.2;">
            <div style="font-size:{{ $idFontSize }}px; color:#2f4482; font-weight:700;">ID No:</div>
            <div style="font-size:{{ $idFontSize }}px; color:#2f4482; font-weight:800;">{{ $user->login_code }}
            </div>
        </div>

    </div>

</div>

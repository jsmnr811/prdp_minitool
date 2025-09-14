@php
    // Base dimensions and scaling factor
    $baseWidth = 300;
    $baseHeight = 490;
    $scale = 3245 / $baseWidth;

    // Split names into first and last name
    $firstNameWords = explode(' ', strtoupper($user->firstname));
    $firstName = count($firstNameWords) > 1 ? $firstNameWords[0] . ' ' . $firstNameWords[1] : $firstNameWords[0];
    $lastName = strtoupper($user->lastname);

    // Dynamic font sizing for the first and last name
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

    // Designation logic
    $designation = strtoupper($user->designation);

    // QR code size and margin
    $qrSize = 50 * $scale;
    $qrMargin = 2;

    // ID number font size
    $idFontSize = 12 * $scale;
@endphp

<div style="width:100vw; height:100vh; display:flex; justify-content:center; align-items:center; background:#f0f0f0;">
    <div
        style="width:{{ $baseWidth * $scale }}px; height:{{ $baseHeight * $scale }}px; position:relative; overflow:hidden; background:#fff; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.2);">

        <!-- Background Image -->
        <img src="{{ $bgSrc }}" alt="ID Background"
            style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:0;">

        <!-- Main content container -->
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
                        style="font-family:'Arial Black', Arial, sans-serif; font-size:{{ $firstFontSize }}px; font-weight:900; color:#2f4482; line-height:1.1; margin-left: 50px;  margin-right: 50px;">
                        {{ $firstName }}
                    </div>
                    <div
                        style="font-family:Arial, sans-serif; font-size:{{ $lastFontSize }}px; font-style:italic; color:#2f4482; line-height:1.1; font-weight:600;">
                        {{ $lastName }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Square Line Separator -->
        @php
            $designationLength = strlen($user->designation);
            $maxFontSize = 350;
            $minFontSize = 150;

            // Adjust font size based on designation length
            if ($designationLength > 10) {
                $fontSize = $maxFontSize - ($designationLength - 10) * 10;
                $fontSize = max($fontSize, $minFontSize);
            } else {
                $fontSize = $maxFontSize;
            }
        @endphp

        <div
            style=" position: relative;  width: 100%; margin-top: 20px; border-radius: 8px; min-height: 500px;
                    display: flex; flex-direction: column; justify-content: center; align-items: center; padding-bottom: {{ 20 * $scale }}px;">

            <!-- Designation: dynamic -->
            <div
                style=" margin-bottom: 5px; color: #2f4482; font-family: Arial, sans-serif; font-size: {{ $fontSize }}px; line-height:1;
            font-weight: 900; text-align: center; margin-left: 70px;  margin-right: 70px; ">
                {{ strtoupper($user->designation) }}
            </div>

            <!-- Office: fixed 100px -->

            <div
                style="font-style: italic; color: #666; font-family: Arial, sans-serif; font-size: 100px; font-weight: 800; text-align: center; line-height:1;">
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

        <!-- QR Code and ID fixed at bottom -->
        <div
            style="position:absolute; bottom:600px; left:50%; transform:translateX(-50%); display:flex; align-items:center; gap:20px; z-index:2;">

            <!-- QR Code -->
            <div
                style="width:{{ $qrSize }}px; height:{{ $qrSize }}px; display:flex; justify-content:center; align-items:center;">
                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size($qrSize)->margin($qrMargin)->generate('https://prdp-pso-nl-auction.da.gov.ph/investment-forum-user-verification/' . $user->id) !!}
            </div>

            <!-- ID Number -->
            <div
                style="font-family:'NeulisSansBold', Arial, sans-serif; line-height:1.2; text-align:left; margin-top: 50px;">
                <div
                    style="font-family:Arial, sans-serif; font-size:{{ $idFontSize }}px; color:#2f4482; font-weight:700;">
                    ID No:</div>
                <div
                    style="font-family:Arial, sans-serif; font-size:{{ $idFontSize }}px; color:#2f4482; font-weight:800;">
                    {{ $user->login_code }}
                </div>
            </div>

        </div>

    </div>
</div>

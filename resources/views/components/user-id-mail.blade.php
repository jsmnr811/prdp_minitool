<div style="width:314px; height:500px; margin:0; padding:0; position:relative; overflow:hidden;">
    <!-- Background Image -->
    <img src="{{ $bgSrc }}" alt="ID Background"
        style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:0;">

    <!-- Content Wrapper -->
    <div style="position:relative; z-index:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-start; padding:20px;">

        <!-- Profile Picture -->
        <div style="margin-top:37px;">
            <img src="{{ $userImageSrc }}" alt="Profile Picture"
                style="display:block; margin:0 auto; width:160px; height:160px; object-fit:cover; border:2px solid rgba(255,255,255,0.8); border-radius:50%;">
        </div>

        <!-- User Info -->
        <div style="display:flex; flex-direction:column; align-items:center; margin-top:5px;">
            @php
            $firstNameWords = explode(' ', strtoupper($user->firstname));
            $firstName = count($firstNameWords) > 1 ? $firstNameWords[0] . ' ' . $firstNameWords[1] : $firstNameWords[0];
            $lastName = strtoupper($user->lastname);

            $firstFontSize = 32;
            $lastFontSize = 18;
            $maxWidth = 250 - 8;

            if (strlen($firstName) > 8) {
            $approxCharWidth = 12;
            $nameWidth = strlen($firstName) * $approxCharWidth;
            while ($nameWidth > $maxWidth && $firstFontSize > 10) {
            $firstFontSize--;
            $approxCharWidth = 0.5 * $firstFontSize;
            $nameWidth = strlen($firstName) * $approxCharWidth;
            }
            }

            $approxCharWidth = 9;
            $nameWidth = strlen($lastName) * $approxCharWidth;
            while ($nameWidth > $maxWidth && $lastFontSize > 8) {
            $lastFontSize--;
            $approxCharWidth = 0.5 * $lastFontSize;
            $nameWidth = strlen($lastName) * $approxCharWidth;
            }
            @endphp

            <div style="text-align:center; margin-top:0px; white-space:nowrap; overflow:hidden; padding-left:4px; padding-right:4px;">
                <div style="font-family:'Arial Black', Arial, sans-serif; font-size: {{ $firstFontSize }}px; font-weight:900; color:#2f4482; line-height:1.1;">
                    {{ $firstName }}
                </div>
                <div style="font-family:Arial, sans-serif; font-size: {{ $lastFontSize }}px; font-style:italic; color:#2f4482; line-height:1.1; font-weight:600;">
                    {{ $lastName }}
                </div>
            </div>

            <div style="text-align:center; font-family: Arial, sans-serif; font-size:1rem; color:#d3e6e9; margin-top:1px; font-weight:800;">
                {{ strtoupper($user->designation) }}
            </div>
        </div>

        <!-- QR Code and ID -->
        <div style="position:relative; margin-top:8px; height:130px; width:100%;">
            <!-- QR Code centered -->
            <div style="width:120px; height:120px; margin:0 auto; display:flex; justify-content:center; align-items:center;">
                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(110)->margin(2)->generate(route('investment.user-verification', ['id' => $user->id])) !!}
            </div>

            <!-- ID Text beside QR -->
            <div style="position:absolute; top:75%; left:60%; transform:translate(-50%, -50%) translateX(80px); font-family:'NeulisSansBold', Arial, sans-serif; font-size:.8rem; color:#2f4482; line-height:1.2; text-align:left;">
                <div style="font-family: Arial, sans-serif; font-size:.8rem; font-weight:700;">ID No:</div>
                <div style="font-family: Arial, sans-serif; font-size:.8rem; font-weight:800;">{{ $user->login_code }}</div>
            </div>
        </div>

    </div>
</div>
<div style="width:314px; height:500px; margin:0; padding:0; position:relative; overflow:hidden;">
    {{-- Background Image --}}
    <img src="{{ $bgSrc }}" alt="ID Background"
        style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:0;">

    {{-- Content Wrapper --}}
    <div
        style="position:relative; z-index:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-start; padding:20px;">

        {{-- Profile Picture --}}
        <div style="margin-top: 37px;">
            <img src="{{ $userImageSrc }}" alt="Profile Picture"
                style="display:block; margin:0 auto; width:160px; height:160px; object-fit:cover; border:2px solid rgba(255,255,255,0.8); border-radius:50%;">
        </div>

        {{-- User Info --}}
        <div style="display:flex; flex-direction:column; align-items:center; margin-top:5px;">
            @php
                $firstNameWords = explode(' ', strtoupper($user->firstname));
                $firstName =
                    count($firstNameWords) > 1 ? $firstNameWords[0] . ' ' . $firstNameWords[1] : $firstNameWords[0];
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

            <div class="name"
                style="text-align:center; margin-top:0px; white-space:nowrap; overflow:hidden; padding-left:4px; padding-right:4px;">
                <div
                    style="font-family:'NeulisBlack', Arial, sans-serif; font-size: {{ $firstFontSize }}px; font-weight:900; color:#2f4482; line-height:1.1;">
                    {{ $firstName }}
                </div>
                <div
                    style="font-family:'NeulisMediumItalic', Arial, sans-serif; font-size: {{ $lastFontSize }}px; font-style:italic; color:#2f4482; line-height:1.1;">
                    {{ $lastName }}
                </div>
            </div>
            <div
                style="text-align:center; font-family:'NeulisSansBold', Arial, sans-serif; font-size:1rem; color:#d3e6e9; margin-top:5px;">
                {{ strtoupper($user->designation) }}
            </div>
        </div>

        <div style="position: relative; margin-top: 15px; height: 130px;">
            {{-- QR Code --}}
            <div
                style="position: absolute; left: 30%; top: 0; transform: translateX(-50%);
        width: 120px; height: 120px; display:flex; justify-content:center; align-items:center;">
                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(110)->margin(2)->generate(route('investment.user-verification', ['id' => $user->id])) !!}
            </div>

            {{-- Text on the right, bottom-aligned with QR --}}
            <div
                style="position: absolute; right: 0%; top: 0; bottom:-50px;
               bottom: 0; font-family:'NeulisSansBold', Arial, sans-serif; font-size:1rem; color:#2f4482;">
                ExampleWord
            </div>
        </div>
    </div>


</div>

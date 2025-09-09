  <div style="position: relative; margin-top: 15px; height: 130px;">

      {{-- QR centered --}}
      <div
          style="position: absolute; left: 50%; top: 0; transform: translateX(-50%);
                width: 120px; height: 120px; display:flex; justify-content:center; align-items:center;">
          {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(110)->margin(2)->generate(route('investment.user-verification', ['id' => $user->id])) !!}
      </div>

      {{-- ID beside QR --}}
      <div
          style="position: absolute; left: calc(50% + 70px); bottom: 15px;
                font-family:'NeulisNeueRegular', Arial, sans-serif;
                color:#2f4482; font-weight:bold; text-align:left; line-height:1.2;">

          <div style="font-size:0.8rem;">ID No:</div>
          <div style="font-family:'NeulisSansBold', Arial, sans-serif; font-size:1em;">
              {{ $user->id_number ?? '5INA4TCN' }}
          </div>
      </div>
  </div>

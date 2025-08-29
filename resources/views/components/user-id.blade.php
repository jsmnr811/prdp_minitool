<style>
    /* Remove all page gaps */
    html,
    body {
        margin: 0;
        padding: 0;
        background: #fff;
    }

    .id-container {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 0;
    }

    .id-card {
        width: 350px;
        height: 566px;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 20px;
        box-sizing: border-box;
        text-align: center;
        font-family: Arial, sans-serif;
        position: relative;
    }

    .header-logos {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .header-logos img {
        height: 40px;
    }

    .event-title {
        font-size: 14px;
        margin-bottom: 12px;
        font-weight: bold;
        text-align: center;
    }

    .profile-pic {
        width: 150px;
        height: 180px;
        object-fit: cover;
        border: 1px solid #ccc;
        display: block;
        margin: 0 auto 8px auto;
    }

    .user-info {
        margin-top: 6px;
        /* tighter gap */
    }

    .user-info .name {
        font-size: 16px;
        font-weight: bold;
    }

    .user-info .role {
        font-size: 13px;
        font-weight: bold;
        margin-top: 2px;
    }

    .user-info .department {
        font-size: 12px;
        margin-top: 1px;
    }

    .footer {
        margin-top: 18px;
        /* reduced */
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        font-size: 12px;
        text-align: left;
        /* optional, aligns the text inside */

    }

    .footer .category,
    .footer .assignment {
        display: inline-block;
        /* makes them inline */
        vertical-align: top;
        /* aligns top edges */
        margin-right: 20px;
        /* spacing between the two */
    }

    .qr-code {
        margin: 6px auto 0 auto;
        /* tighter gap above */
        width: 80px;
        height: 80px;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 3px solid #000;
        /* solid black border */
        box-sizing: border-box;
    }

    .qr-code svg {
        display: block;
        max-width: 100%;
        height: auto;
    }

    .powered {
        position: absolute;
        bottom: 6px;
        left: 0;
        right: 0;
        font-size: 10px;
        color: #555;
    }
</style>

<div class="id-container">
    <div class="id-card">
        <!-- Logos -->
        <div class="header-logos">
            <img src="{{ asset('media/Scale-Up.png') }}" alt="Logo1">
        </div>

        <!-- Event title -->
        <div class="event-title">
            National Agri-Fishery Investment Forum
        </div>

        <!-- Profile Picture -->
        <img class="profile-pic" src="{{ asset($user->image) }}" alt="Profile Picture">

        <!-- User Information -->
        <div class="user-info">
            <div class="name">{{ $user->name }}</div>
            <div class="role">{{ $user->position }}</div>
            <div class="department">{{ $user->department }}</div>
        </div>

        <!-- QR Code -->
        @php
            $qrCode = SimpleSoftwareIO\QrCode\Facades\QrCode::size(72)
                ->margin(2)
                ->generate(route('investment.user-verification', ['id' => $user->id]));
        @endphp
        <div class="qr-code">
            {!! $qrCode !!}
        </div>
        <div class="text-center text-muted" style="font-size: 0.5rem; margin-top: 0.7rem;">
            ID #: {{ $user->login_code }}
        </div>


        <!-- Footer -->
        <div class="footer">
            <div class="category">
                Office: {{ $user->office }} <br>
                Designation:<br>
                {{ $user->designation }}
            </div>
            <div class="assignment">
                Assignment:<br>
                Group: {{ $user->group_number }}<br>
                Seat: {{ $user->table_number }}
            </div>
        </div>

        <div class="powered">Powered by: DA-PRDP</div>
    </div>
</div>

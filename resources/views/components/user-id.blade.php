<style>
    html,
    body {
        margin: 0;
        padding: 0;
        background: #fff;
    }

    .id-container {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .id-card {
        width: 350px;
        height: 566px;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 20px;
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
        font-weight: bold;
        margin-bottom: 12px;
    }

    .profile-pic {
        width: 150px;
        height: 180px;
        object-fit: cover;
        border: 1px solid #ccc;
        margin: 0 auto 8px auto;
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
        display: flex;
        justify-content: space-between;
        font-size: 12px;
    }

    .qr-code {
        margin: 6px auto 0;
        width: 80px;
        height: 80px;
        border: 3px solid #000;
        display: flex;
        justify-content: center;
        align-items: center;
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
        <div class="header-logos">
            <img src="{{ public_path('media/Scale-Up.png') }}" alt="Logo1">
        </div>

        <div class="event-title">National Agri-Fishery Investment Forum</div>

        @php
            $imagePath =
                $user->image && Storage::disk('public')->exists($user->image)
                    ? asset('storage/' . $user->image)
                    : asset('storage/investmentforum2025/default.png');
        @endphp

        <img class="profile-pic" src="{{ $imagePath }}" alt="Profile Picture">


        <div class="user-info">
            <div class="name">{{ $user->name }}</div>
            <div class="role">{{ $user->position }}</div>
            <div class="department">{{ $user->department }}</div>
        </div>

        <div class="qr-code">
            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(72)->margin(2)->generate(route('investment.user-verification', ['id' => $user->id])) !!}
        </div>

        <div class="text-center text-muted" style="font-size:0.5rem; margin-top:0.7rem;">
            ID #: {{ $user->login_code }}
        </div>

        <div class="footer">
            <div class="category">
                Office: {{ $user->office }}<br>
                Designation:<br>{{ $user->designation }}
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

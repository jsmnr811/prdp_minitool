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
        height: 525px;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        font-family: Arial, sans-serif;
        position: relative;
        overflow: visible;
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
        margin: 12px auto 12px auto;
    }

    .profile-pic {
        width: 150px;
        height: 180px;
        object-fit: cover;
        border: 1px solid #ccc;
        margin: 10px auto 10px auto;
    }

    .user-info .name {
        font-size: 24px;
        font-weight: bold;
    }

    .user-info .role {
        font-size: 10px;
        font-weight: bold;
        margin-top: 2px;
    }

    .user-info .department {
        font-size: 12px;
        margin-top: 1px;
    }

    .footer {
        margin-top: 18px;
        font-size: 12px;
        text-align: left;
        display: block;
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
            <img src="{{ $logoSrc }}" alt="Logo1">
        </div>

        <div class="event-title">National Agri-Fishery Investment Forum</div>
        <img class="profile-pic" src="{{ $userImageSrc }}" alt="Profile Picture">

        <div class="user-info">
            <div class="name">{{ strtoupper($user->name) }}</div>
            <div class="role" style="font-size:1rem;">{{ strtoupper($user->designation) }}</div>
        </div>

        <div class="qr-code" style="font-size:0.7rem; margin-top:15px">
            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(72)->margin(2)->generate(route('investment.user-verification', ['id' => $user->id])) !!}
        </div>

        <div class="text-center text-muted" style="font-size:0.7rem; margin-top:15px">
            ID #: {{ $user->login_code }}
        </div>

        <div class="powered">Powered by: DA-PRDP</div>
    </div>
</div>

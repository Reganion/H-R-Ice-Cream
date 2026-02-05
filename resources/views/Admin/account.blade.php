@extends('admin.layout.layout')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @section('title', 'Account Management')

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        /* =========================
           CONTENT AREA
        ========================== */
        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 10px 10px 0;
            overflow: hidden;
            background: #f2f2f2;
            border-top-left-radius: 30px;
            min-height: 0;
        }

        /* =========================
           ACCOUNT PAGE LAYOUT
        ========================== */
        .account-page {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }


        /* HEADER */
        .account-header {
            margin-bottom: 20px;
            flex-shrink: 0;
        }

        .account-header h2 {
            font-size: 22px;
            font-weight: 600;
        }

        /* BODY */
        .account-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }


        /* SCROLLABLE CARD */
        .account-scroll {
            flex: 1;
            overflow-y: auto;
            padding-right: 6px;
            padding-bottom: 0;
        }


        .account-card {
            background: #fff;
            border-top-right-radius: 24px;
            border-top-left-radius: 24px;
            padding: 35px 40px;
            box-sizing: border-box;
            margin: 0;

            min-height: 100%;
            display: flex;
            flex-direction: column;
        }



        .account-card-scroll {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding-right: 6px;
        }



        /* =========================
           PROFILE
        ========================== */
        .profile-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 35px;
        }

        .profile-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-left img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-info h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .profile-info small {
            color: #888;
            font-size: 14px;
        }

        .profile-actions {
            display: flex;
            gap: 12px;
        }

        .btn-outline {
            padding: 10px 18px;
            border-radius: 12px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-light {
            padding: 10px 18px;
            border-radius: 12px;
            border: none;
            background: #f2f2f2;
            cursor: pointer;
            font-weight: 500;
        }

        /* =========================
           FORM
        ========================== */


        .form-section h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }


        .form-group label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .divider {
            height: 1px;
            background: #eee;
            margin: 25px 0;
        }

        .password-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 24px;
            align-items: end;
        }


        .password-row .form-group {
            margin-bottom: 0;
        }

        .change-btn {
            height: 48px;
            padding: 0 22px;
            border-radius: 12px;
            border: none;
            background: #f2f2f2;
            font-weight: 500;
            cursor: pointer;
        }


        /* =========================
           RESPONSIVE
        ========================== */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .content-area {
                padding: 8px;
                border-radius: 0;
            }

            .account-card {
                padding: 22px;
                border-radius: 24px;
            }
        }

        .form-section,
        .form-row,
        .password-row {
            width: 100%;
        }

        .single-col {
            max-width: 49%;
        }

        .single-cols {
            max-width: 57%;
        }
    </style>
</head>

<body>
    @section('content')

        <div class="content-area account-page">

            <!-- HEADER -->
            <div class="account-header">
                <h2>Account Settings</h2>
            </div>

            <!-- BODY -->
            <div class="account-body">

                <!-- SCROLLABLE -->
                <div class="account-scroll">
                    <div class="account-card">

                        <!-- PROFILE -->
                        <div class="profile-row">
                            <div class="profile-left">
                                <img src="{{ $adminUser && $adminUser->image ? asset($adminUser->image) : asset('img/kyle.jpg') }}" alt="Profile">
                                <div class="profile-info">
                                    <h4>Profile Picture</h4>
                                    <small>Profile picture</small>
                                </div>
                            </div>

                            <div class="profile-actions">
                                <button class="btn-outline">Update Account</button>
                                <button class="btn-light">Delete</button>
                            </div>
                        </div>

                        <div class="form-row form-content">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" value="{{ $adminUser?->first_name ?? '' }}" disabled>
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" value="{{ $adminUser?->last_name ?? '' }}" disabled>
                            </div>
                        </div>


                        <div class="divider"></div>

                        <div class="form-section">
                            <h4>Contact Email</h4>

                            <div class="form-group single-col">
                                <label>Email Address</label>
                                <input type="email" value="{{ $adminUser?->email ?? '' }}" disabled>
                            </div>
                        </div>


                        <div class="divider"></div>
                        <div class="form-section">
                            <h4>Password</h4>

                            <div class="password-row">
                                <div class="form-group single-cols">
                                    <label>Current Password</label>
                                    <input type="password" placeholder="Enter current password">
                                </div>

                                <button class="change-btn">Change password</button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    @endsection
</body>

</html>

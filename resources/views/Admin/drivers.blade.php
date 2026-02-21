@extends('admin.layout.layout')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    @section('title', 'Driver Management')
    <link rel="stylesheet" href="{{ asset('assets/css/Admin/app.css') }}">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 10px;
            overflow: hidden;
            background: rgb(242, 242, 242);
            border-top-left-radius: 30px;
            min-height: 0;
        }


        /* =======================
        DRIVER PAGE
        ======================= */

        .driver-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;

        }

        .driver-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }


        .driver-header h2 {
            font-size: 22px;
            font-weight: 600;
        }

        .driver-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }


        .driver-tabs {
            display: inline-flex;
            background: #fff;

            border-radius: 10px;
        }

        .driver-tabs button {
            border: none;
            background: transparent;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #000;
            cursor: pointer;
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .driver-tabs button.active {
            background: #0066ff;
            color: #fff;
            box-shadow: 0 2px 6px rgba(0, 102, 255, 0.35);
        }

        .driver-tabs button:not(.active):hover {
            color: #000;
        }


        .driver-search {
            position: relative;
        }

        .driver-search input {
            padding: 12px 40px;
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
            width: 320px;
        }

        .driver-search span {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #888;
        }

        .btn-add {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            background: #0066ff;
            color: #fff;
            border: none;

            padding: 12px 20px;
            border-radius: 12px;

            font-size: 14px;
            font-weight: 600;

            cursor: pointer;
            white-space: nowrap;

            box-shadow: 0 6px 16px rgba(0, 102, 255, 0.35);
            transition: all 0.25s ease;
        }

        .btn-add span.material-symbols-outlined {
            font-size: 20px;
            line-height: 1;
        }

        .btn-add:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(0, 102, 255, 0.45);
        }

        .btn-add:active {
            transform: translateY(0);
            box-shadow: 0 4px 10px rgba(0, 102, 255, 0.35);
        }


        /* =======================
        DRIVER GRID
        ======================= */

        .driver-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            align-items: stretch;
        }

        .driver-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
        }

        .driver-card img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .driver-card h4 {
            margin-bottom: 10px;
            font-size: 16px;
        }

        .driver-tags {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 15px;
            align-items: center;
        }

        .driver-tags .code {
            background: #eee;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 400;
        }

        .card-delete-btn {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            border: none;
            background: #fee2e2;
            color: #dc2626;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
            position: absolute;
            top: 12px;
            right: 12px;
        }

        .card-delete-btn .material-symbols-outlined {
            font-size: 18px;
            line-height: 1;
        }

        .card-delete-btn:hover {
            background: #fecaca;
            transform: translateY(-1px);
        }

        .status {
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 400;
        }

        .status.on {
            background: #cce0ff;
            color: #0056ff;

        }

        .status.off {
            background: #ffd6d6;
            color: #d40000;
        }

        .status.available {
            background: #d4f5e9;
            color: #0b8f5a;
        }

        .status.deactivate {
            background: #e5e7eb;
            color: #6b7280;
        }

        .driver-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 13px;
            color: #555;
            margin-bottom: 15px;
        }


        .driver-info p {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0;
        }

        .driver-info p strong {
            font-weight: 600;
            color: #555;
        }

        .driver-info p span {
            color: #000;
            text-align: right;
            font-weight: 500;
            max-width: 70%;
            min-width: 0;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .driver-info p span.driver-email {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: normal;
            overflow-wrap: normal;
        }

        .driver-status-row {
            margin-top: auto;
            padding-top: 12px;
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .driver-status-row .status {
            width: 100%;
            border-radius: 30px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            box-sizing: border-box;
        }

        /* =======================
        PAGINATION
        ======================= */


        .drivers-page {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        /* body area */
        .drivers-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        /* scrollable grid */
        .drivers-scroll {
            flex: 1;
            overflow-y: auto;
            padding-right: 6px;
        }

        /* =======================
        PAGINATION (ORDERS STYLE)
        ======================= */

        .pagination-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 14px;
            padding: 14px 0 6px;
            border-top: 1px solid #eee;
            flex-shrink: 0;
            background: #f2f2f2;
        }


        /* Prev / Next buttons */
        .page-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #fff;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            white-space: nowrap;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .page-btn .material-symbols-outlined {
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }

        .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .page-btn:hover:not(:disabled) {
            background: #f9fafb;
            border-color: #d1d5db;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        /* Page numbers */
        .page-numbers {
            display: flex;
            gap: 10px;
        }

        .page-numbers span {
            border: none;
            background: transparent;
            font-size: 14px;
            color: #9ca3af;
            padding: 6px 10px;
            cursor: pointer;
        }

        .page-numbers span.active {
            color: #000;
            font-weight: 600;
        }


        @media (max-width: 900px) {
            .driver-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .driver-left {
                flex-wrap: wrap;
            }

            .driver-actions {
                width: 100%;
                justify-content: space-between;
            }
        }

        /* =======================
   MOBILE RESPONSIVENESS
======================= */

        @media (max-width: 768px) {

            /* CONTENT */
            .content-area {
                padding: 8px;
                border-top-left-radius: 18px;
            }

            /* HEADER STACK */
            .driver-header {
                gap: 14px;
                margin-bottom: 16px;
            }

            .driver-left {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                width: 100%;
            }

            .driver-header h2 {
                font-size: 18px;
            }

            /* TABS → SCROLLABLE */
            .driver-tabs {
                width: 100%;
                overflow-x: auto;
                padding: 4px;
            }

            .driver-tabs::-webkit-scrollbar {
                display: none;
            }

            .driver-tabs button {
                padding: 6px 12px;
                font-size: 12px;
                flex-shrink: 0;
            }

            /* ACTIONS */
            .driver-actions {
                width: 100%;
                gap: 10px;
            }

            .driver-search {
                width: 100%;
            }

            .driver-search input {
                width: 100%;
                font-size: 14px;
            }

            .btn-add {
                width: 100%;
                padding: 14px;
                font-size: 15px;
                border-radius: 14px;
            }

            /* GRID → SINGLE COLUMN */
            .driver-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            /* CARD */
            .driver-card {
                padding: 16px;
                border-radius: 16px;
            }

            .driver-card img {
                width: 60px;
                height: 60px;
            }

            .driver-card h4 {
                font-size: 15px;
            }

            /* INFO */
            .driver-info {
                gap: 6px;
                font-size: 13px;
            }

            .driver-info p span {
                max-width: 60%;
                text-align: right;
                word-break: break-word;
            }

            /* PAGINATION */
            .pagination-wrapper {
                flex-wrap: wrap;
                gap: 10px;
                padding: 10px 0;
            }

            .page-numbers {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        /* =======================
        DELETE CONFIRM MODAL (same style as flavors)
        ======================= */
        .delete-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 4000;
        }

        .delete-overlay.show {
            display: flex;
        }

        .delete-modal {
            background: #fff;
            width: 420px;
            max-width: calc(100% - 32px);
            border-radius: 16px;
            padding: 26px;
            text-align: left;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .delete-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 10px;
        }

        .delete-title span {
            font-weight: 700;
        }

        .delete-text {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .delete-actions {
            display: flex;
            justify-content: center;
            gap: 14px;
        }

        .btn-delete-cancel {
            padding: 10px 18px;
            border-radius: 999px;
            border: none;
            background: #f3f4f6;
            color: #374151;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-delete-confirm {
            padding: 10px 18px;
            border-radius: 999px;
            border: none;
            background: #ef4444;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-delete-confirm:hover {
            background: #dc2626;
        }

        @media (max-width: 600px) {
            .delete-modal {
                width: calc(100% - 24px);
                max-width: none;
                padding: 20px 16px;
                border-radius: 14px;
            }

            .delete-actions {
                flex-direction: column;
                gap: 10px;
            }

            .btn-delete-cancel,
            .btn-delete-confirm {
                width: 100%;
                min-height: 42px;
            }
        }

        /* ======================= ADD DRIVER MODAL (same pattern as flavor) ======================= */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
            z-index: 3000;
        }
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        .modal-card {
            position: fixed;
            top: 20px;
            right: 20px;
            bottom: 20px;
            width: 460px;
            max-width: calc(100% - 40px);
            max-height: calc(100vh - 40px);
            background: #fff;
            border-radius: 32px;
            padding: 24px 24px 18px;
            display: flex;
            flex-direction: column;
            transform: translateX(120%);
            opacity: 0;
            transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.3s ease;
        }
        .modal-overlay.show .modal-card {
            transform: translateX(0);
            opacity: 1;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 18px;
            border-bottom: 1px solid #f0f0f0;
        }
        .modal-header h3 { font-size: 18px; font-weight: 600; color: #111827; }
        .modal-close { background: transparent; border: none; cursor: pointer; color: #6b7280; }
        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .upload-box { display: flex; align-items: center; gap: 18px; }
        .upload-preview {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            overflow: hidden;
        }
        .upload-preview img { width: 100%; height: 100%; object-fit: cover; }
        .btn-upload {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            background: #0066ff;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }
        .modal-driver .form-group { display: flex; flex-direction: column; gap: 8px; width: 100%; }
        .modal-driver .form-group label { font-size: 13px; color: #6b7280; }
        .modal-driver .text-danger { color: #dc3545; }
        .modal-driver .field-error { font-size: 12px; color: #dc3545; margin-top: 4px; }
        .modal-driver input.invalid { border-color: #dc3545; }
        .modal-driver .custom-select.invalid .select-trigger { border-color: #dc3545; }
        .modal-driver .form-group input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
            box-sizing: border-box;
        }
        .modal-driver .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .modal-driver .custom-select {
            position: relative;
            width: 100%;
        }
        .modal-driver .select-trigger {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #fff;
            font-size: 14px;
            color: #6b7280;
            cursor: pointer;
        }
        .modal-driver .select-options {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 12px 28px rgba(0,0,0,0.08);
            display: none;
            z-index: 2000;
        }
        .modal-driver .custom-select.open .select-options { display: block; }
        .modal-driver .custom-select.open .select-trigger { border-color: #2563eb; }
        .modal-driver .option { padding: 14px 16px; font-size: 14px; cursor: pointer; }
        .modal-driver .option:hover { background: #f9fafb; }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 18px;
            border-top: 1px solid #f0f0f0;
        }
        .btn-cancel {
            padding: 10px 20px;
            border-radius: 12px;
            border: none;
            background: #f3f4f6;
            color: #374151;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-save {
            padding: 10px 20px;
            border-radius: 12px;
            border: none;
            background: #0066ff;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>

<body>
    @section('content')
        @include('admin.partials.alert')
        <div class="content-area drivers-page">

            <!-- HEADER -->
            <div class="driver-header">

                <!-- LEFT SIDE -->
                <div class="driver-left">
                    <h2>Driver list</h2>

                    @php
                        $statusFilterMap = ['available' => 'available', 'on_route' => 'on', 'off_duty' => 'off'];
                        $driverCountAll = $drivers->count();
                        $driverCountAvailable = $drivers->where('status', 'available')->count();
                        $driverCountOnRoute = $drivers->where('status', 'on_route')->count();
                        $driverCountOffDuty = $drivers->where('status', 'off_duty')->count();
                    @endphp
                    <div class="driver-tabs">
                        <button class="active" data-filter="all">All ({{ $driverCountAll }})</button>
                        <button data-filter="available">Available ({{ $driverCountAvailable }})</button>
                        <button data-filter="on">On Route ({{ $driverCountOnRoute }})</button>
                        <button data-filter="off">Off Duty ({{ $driverCountOffDuty }})</button>
                    </div>

                </div>

                <!-- RIGHT SIDE -->
                <div class="driver-actions">
                    <div class="driver-search">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" placeholder="Search by driver name">
                    </div>

                    <button class="btn-add" id="addDriverBtn">
                        <span class="material-symbols-outlined">add</span> Add New Driver
                    </button>
                </div>

            </div>


            <!-- PAGE BODY -->
            <div class="drivers-body">

                <!-- SCROLLABLE GRID -->
                <div class="drivers-scroll">
                    <div class="driver-grid">
                        @php
                            $statusMap = ['available' => 'available', 'on_route' => 'on', 'off_duty' => 'off', 'deactivate' => 'deactivate'];
                            $statusLabel = ['available' => 'Available', 'on_route' => 'On Route', 'off_duty' => 'Off Duty', 'deactivate' => 'Deactivate'];
                        @endphp
                        @forelse ($drivers as $driver)
                            @php
                                $driverStatus = $driver->status ?? 'available';
                                $filterValue = $statusMap[$driverStatus] ?? 'available';
                                $labelValue = $statusLabel[$driverStatus] ?? 'Available';
                            @endphp
                            <div class="driver-card" data-status="{{ $filterValue }}">
                                <button type="button" class="card-delete-btn" title="Remove driver" aria-label="Remove driver" data-driver-id="{{ $driver->id }}" data-driver-name="{{ $driver->name }}">
                                    <span class="material-symbols-outlined">remove</span>
                                </button>
                                <img src="{{ (isset($driver->image) && $driver->image) ? asset($driver->image) : asset('img/default-user.png') }}" alt="{{ $driver->name ?? '' }}">
                                <h4>{{ $driver->name }}</h4>
                                <div class="driver-tags">
                                    <span class="code">{{ $driver->driver_code ?? 'DRV' . str_pad((string)$driver->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="driver-info">
                                    <p><strong>Phone</strong><span>{{ $driver->phone ?? '—' }}</span></p>
                                    <p><strong>Email</strong><span class="driver-email">{{ $driver->email ?? '—' }}</span></p>
                                    <p><strong>License No.</strong><span>{{ $driver->license_no ?? '—' }}</span></p>
                                    <p><strong>Type</strong><span>{{ $driver->license_type ?? '—' }}</span></p>
                                </div>
                                <div class="driver-status-row">
                                    <span class="status {{ $filterValue }}">{{ $labelValue }}</span>
                                </div>
                            </div>
                        @empty
                            <div style="grid-column:1/-1;text-align:center;padding:40px;color:#666;">
                                No drivers yet. Click "Add New Driver" to add one.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- PAGINATION (STICKS BOTTOM) -->
                <div class="pagination-wrapper">
                    <button class="page-btn prev" disabled>
                        <span class="material-symbols-outlined">arrow_left_alt</span>
                        Previous
                    </button>
                    <div class="page-numbers" id="pageNumbers"></div>
                    <button class="page-btn next">
                        Next
                        <span class="material-symbols-outlined">arrow_right_alt</span>
                    </button>
                </div>

            </div>

        </div>

        <!-- ADD NEW DRIVER MODAL -->
        <div class="modal-overlay" id="addDriverModal">
            <form class="modal-card modal-driver" action="{{ route('admin.drivers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h3>Add New Driver</h3>
                    <button type="button" class="modal-close" id="closeAddDriverModal">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="upload-box">
                        <div class="upload-preview" id="driverImagePreview">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                        <div>
                            <div class="form-group" style="margin-bottom:8px;">
                                <label>Profile Picture</label>
                            </div>
                            <input type="file" id="driverImage" name="image" accept="image/*" hidden>
                            <button type="button" class="btn-upload" onclick="document.getElementById('driverImage').click()">Upload</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Driver Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" placeholder="Enter Name" value="{{ old('name') }}" maxlength="255" class="{{ $errors->has('name') ? 'invalid' : '' }}">
                        @error('name')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" placeholder="Enter Phone Number" value="{{ old('phone') }}" maxlength="50" class="{{ $errors->has('phone') ? 'invalid' : '' }}">
                            @error('phone')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" placeholder="Enter Email" value="{{ old('email') }}" maxlength="255" class="{{ $errors->has('email') ? 'invalid' : '' }}">
                            @error('email')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>License No. <span class="text-danger">*</span></label>
                            <input type="text" name="license_no" placeholder="Enter License No." value="{{ old('license_no') }}" maxlength="100" class="{{ $errors->has('license_no') ? 'invalid' : '' }}">
                            @error('license_no')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>License Type <span class="text-danger">*</span></label>
                            <div class="custom-select {{ $errors->has('license_type') ? 'invalid' : '' }}" id="licenseTypeSelect">
                                <div class="select-trigger">
                                    <span class="selected-text">Select Type</span>
                                    <span class="material-symbols-outlined">expand_more</span>
                                </div>
                                <div class="select-options">
                                    <div class="option" data-value="Professional">Professional</div>
                                    <div class="option" data-value="Non-Professional">Non-Professional</div>
                                </div>
                                <input type="hidden" name="license_type" id="licenseTypeInput" value="{{ old('license_type') }}">
                            </div>
                            @error('license_type')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelAddDriver">Cancel</button>
                    <button type="submit" class="btn-save">Add Driver</button>
                </div>
            </form>
        </div>

        <div class="delete-overlay" id="deleteDriverConfirmModal">
            <form class="delete-modal" method="POST" id="deleteDriverForm">
                @csrf
                @method('DELETE')

                <h3 class="delete-title">
                    Are you sure you want to remove
                    <span id="deleteDriverName">this driver</span>?
                </h3>

                <p class="delete-text">
                    If you remove this driver, the status will be set to deactivate and it will no longer show in this list.
                </p>

                <div class="delete-actions">
                    <button type="button" class="btn-delete-cancel" id="cancelDriverDelete">
                        No, Cancel
                    </button>

                    <button type="submit" class="btn-delete-confirm">
                        Yes, I'm sure
                    </button>
                </div>
            </form>
        </div>

        </div>
    @endsection
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const pendingAlertMessage = sessionStorage.getItem("driverPendingAlertMessage");
            const pendingAlertType = sessionStorage.getItem("driverPendingAlertType");
            if (
                pendingAlertMessage &&
                typeof window.showGlobalAlert === "function" &&
                !document.getElementById("globalAlert")
            ) {
                window.showGlobalAlert(pendingAlertMessage, pendingAlertType || "success");
            }
            sessionStorage.removeItem("driverPendingAlertMessage");
            sessionStorage.removeItem("driverPendingAlertType");

            const allCards = Array.from(document.querySelectorAll(".driver-card"));
            const tabs = document.querySelectorAll(".driver-tabs button");
            const searchInput = document.querySelector(".driver-search input");

            const pageNumbers = document.getElementById("pageNumbers");
            const prevBtn = document.querySelector(".page-btn.prev");
            const nextBtn = document.querySelector(".page-btn.next");

            const itemsPerPage = 10;
            let currentPage = 1;
            let activeFilter = "all";
            let filteredCards = [...allCards];

            function applyEmailOverflowTooltip() {
                const emailEls = document.querySelectorAll(".driver-email");
                emailEls.forEach((el) => {
                    // Add tooltip only when text is visually truncated.
                    if (el.scrollWidth > el.clientWidth) {
                        el.title = el.textContent.trim();
                    } else {
                        el.removeAttribute("title");
                    }
                });
            }

            /* =========================
               APPLY FILTERS (TAB + SEARCH)
            ========================= */
            function applyFilters() {
                const searchValue = searchInput.value.toLowerCase();

                filteredCards = allCards.filter(card => {
                    const statusMatch =
                        activeFilter === "all" ||
                        card.dataset.status === activeFilter;

                    const name = card.querySelector("h4").textContent.toLowerCase();
                    const searchMatch = name.includes(searchValue);

                    return statusMatch && searchMatch;
                });

                currentPage = 1;
                renderPage();
            }

            /* =========================
               TAB FILTER
            ========================= */
            tabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    tabs.forEach(t => t.classList.remove("active"));
                    tab.classList.add("active");

                    activeFilter = tab.dataset.filter;
                    applyFilters();
                });
            });

            /* =========================
               SEARCH
            ========================= */
            searchInput.addEventListener("input", applyFilters);

            /* =========================
               PAGINATION
            ========================= */
            function renderPage() {
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + itemsPerPage;

                allCards.forEach(card => card.style.display = "none");

                filteredCards.slice(start, end).forEach(card => {
                    card.style.display = "block";
                });

                renderPagination();
                applyEmailOverflowTooltip();
            }

            function renderPagination() {
                pageNumbers.innerHTML = "";
                const totalPages = Math.ceil(filteredCards.length / itemsPerPage);

                for (let i = 1; i <= totalPages; i++) {
                    const span = document.createElement("span");
                    span.textContent = i;
                    if (i === currentPage) span.classList.add("active");

                    span.addEventListener("click", () => {
                        currentPage = i;
                        renderPage();
                    });

                    pageNumbers.appendChild(span);
                }

                prevBtn.disabled = currentPage === 1;
                nextBtn.disabled = currentPage === totalPages || totalPages === 0;
            }

            prevBtn.addEventListener("click", () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage();
                }
            });

            nextBtn.addEventListener("click", () => {
                const totalPages = Math.ceil(filteredCards.length / itemsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderPage();
                }
            });

            renderPage();
            window.addEventListener("resize", applyEmailOverflowTooltip);

            /* ===================== ADD DRIVER MODAL ===================== */
            const addDriverModal = document.getElementById("addDriverModal");
            const addDriverForm = addDriverModal?.querySelector("form");

            addDriverForm?.addEventListener("submit", () => {
                sessionStorage.setItem("driverPendingAlertMessage", "Driver added successfully");
                sessionStorage.setItem("driverPendingAlertType", "success");
            });

            function closeDriverModalSmooth() {
                const card = addDriverModal.querySelector(".modal-card");
                card.style.transform = "translateX(120%)";
                card.style.opacity = "0";
                setTimeout(() => {
                    addDriverModal.classList.remove("show");
                    card.style.transform = "";
                    card.style.opacity = "";
                }, 300);
            }

            document.getElementById("addDriverBtn").onclick = () => {
                addDriverModal.classList.add("show");
            };

            document.getElementById("closeAddDriverModal").onclick = closeDriverModalSmooth;
            document.getElementById("cancelAddDriver").onclick = closeDriverModalSmooth;

            addDriverModal.onclick = (e) => {
                if (e.target === addDriverModal) closeDriverModalSmooth();
            };

            document.getElementById("driverImage").addEventListener("change", function() {
                const file = this.files[0];
                const preview = document.getElementById("driverImagePreview");
                if (!file) return;
                const reader = new FileReader();
                reader.onload = () => {
                    preview.innerHTML = `<img src="${reader.result}" alt="Preview">`;
                };
                reader.readAsDataURL(file);
            });

            const licenseSelect = document.getElementById("licenseTypeSelect");
            const licenseTrigger = licenseSelect.querySelector(".select-trigger");
            const licenseSelected = licenseSelect.querySelector(".selected-text");
            const licenseInput = document.getElementById("licenseTypeInput");
            licenseSelect.querySelectorAll(".option").forEach(opt => {
                opt.onclick = () => {
                    licenseInput.value = opt.dataset.value;
                    licenseSelected.textContent = opt.textContent;
                    licenseSelect.classList.remove("open");
                };
            });
            licenseTrigger.onclick = (e) => {
                e.preventDefault();
                document.querySelectorAll(".modal-driver .custom-select").forEach(s => {
                    if (s !== licenseSelect) s.classList.remove("open");
                });
                licenseSelect.classList.toggle("open");
            };
            document.addEventListener("click", (e) => {
                if (!licenseSelect.contains(e.target)) licenseSelect.classList.remove("open");
            });

            const deleteDriverModal = document.getElementById("deleteDriverConfirmModal");
            const deleteDriverName = document.getElementById("deleteDriverName");
            const deleteDriverForm = document.getElementById("deleteDriverForm");
            const cancelDriverDelete = document.getElementById("cancelDriverDelete");

            deleteDriverForm?.addEventListener("submit", () => {
                sessionStorage.setItem("driverPendingAlertMessage", "Driver removed successfully");
                sessionStorage.setItem("driverPendingAlertType", "success");
            });

            document.querySelectorAll(".card-delete-btn").forEach((btn) => {
                btn.addEventListener("click", () => {
                    const driverId = btn.dataset.driverId;
                    const driverName = btn.dataset.driverName || "this driver";

                    deleteDriverName.textContent = driverName;
                    deleteDriverForm.action = `/admin/drivers/${driverId}`;
                    deleteDriverModal.classList.add("show");
                });
            });

            cancelDriverDelete.addEventListener("click", () => {
                deleteDriverModal.classList.remove("show");
            });

            deleteDriverModal.addEventListener("click", (e) => {
                if (e.target === deleteDriverModal) {
                    deleteDriverModal.classList.remove("show");
                }
            });
        });

        document.addEventListener("DOMContentLoaded", () => {
            const alert = document.getElementById("globalAlert");
            if (!alert) return;
            setTimeout(() => {
                alert.style.transition = "opacity 0.3s ease, transform 0.3s ease";
                alert.style.opacity = "0";
                alert.style.transform = "translateY(10px)";
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        });
    </script>


</body>

</html>

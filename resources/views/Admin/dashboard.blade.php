@extends('admin.layout.layout')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="icon" href="{{ asset('img/logo.png') }}" />
    @section('title', 'Dashboard')
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">


    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            /* ⬅ prevents page scroll */
        }

        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 10px;
            overflow: hidden;
            background: rgb(242, 242, 242);
            border-top-left-radius: 30px;
            margin: 0;
            box-shadow: none;
            position: relative;
            min-height: 0;
        }

        /* SUMMARY CARDS */
        .summary-boxes {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .summary-box {
            background: white;
            padding: 20px;
            border-radius: 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .summary-box h4 {
            color: #7b7b7b;
            font-size: 15px;
            margin-bottom: 8px;
        }

        .summary-box h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .summary-box h2 span.icon {
            width: 10px;
            height: 3px;
            border-radius: 2px;
            display: inline-block;
        }

        .summary-box p {
            color: #b0b0b0;
            font-size: 14px;
        }

        /* TAB BUTTON STYLE */
        .order-tabs {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px 110px;
            margin-bottom: 30px;
            border-bottom: none;
            padding-bottom: 0;
        }

        /* Smooth hover animation */
        .tab-btn {
            background: none;
            border: none;
            font-size: 18px;
            padding-bottom: 8px;
            cursor: pointer;
            color: black;
            position: relative;
            transition: color 0.3s ease;
        }

        .tab-btn::after {
            content: "";
            position: absolute;
            left: 20%;
            bottom: 0;
            width: 0;
            height: 2px;
            background: #d23f3f;
            transition: all 0.3s ease;
            transform: translateX(-20%);
        }

        .tab-btn:hover {
            color: #d23f3f;
        }

        .tab-btn:hover::after {
            width: 100%;
        }

  
/* ACTIVE TAB – include the word */
.tab-btn.active {
    color: #d23f3f;
    font-weight: 600;
}


/* Keep underline */
.tab-btn.active::after {
    width: 40%;
    
}



        .date-filter {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .date-input-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            /* space between start → to → end */
        }

        .date-input-wrapper input[type="date"] {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            background: #fff;
            cursor: pointer;
        }

        /* ⭐ The display box */
        .date-filter-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #ddd;
            min-width: 270px;
        }

        /* Icon styling */
        .date-filter-box .material-symbols-outlined {
            font-size: 20px;
            color: #5a5a5a;
        }

        /* Displayed formatted date */
        .formatted-date {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }



        .orders-table {
            width: 100%;
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
            padding: 0;
        }

        /* Scrollable area */
        .table-scroll {
            flex: 1;
            min-height: 0;
            overflow-x: auto;
            overflow-y: auto;
            padding-right: 6px;
        }


        .table-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .table-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-scroll::-webkit-scrollbar-thumb {
            background: rgba(136, 136, 136, 0);
            border-radius: 4px;
            transition: background 0.3s;
        }


        .table-scroll:hover::-webkit-scrollbar-thumb {
            background: #888;
        }


        .table-scroll:hover::-webkit-scrollbar-thumb:hover {
            background: #555;
        }


        .table-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(136, 136, 136, 0) #f1f1f1;
        }

        .table-scroll:hover {
            scrollbar-color: #888 #f1f1f1;
        }

        /* table basics */
        .orders-table table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
            min-width: 700px;
        }



        .orders-table thead th {
            padding: 15px 10px;
            font-size: 14px;
            color: #6f6f6f;
            font-weight: 600;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 2;
            background: #ffffff;
        }

        .orders-table thead .material-symbols-outlined {
            font-size: 18px;
            color: #000000b3;
        }

        .th-content {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            line-height: 1;
        }

        .orders-table thead th {
            vertical-align: middle;
        }

        .orders-table thead .material-symbols-outlined {
            font-size: 18px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }


        @media (max-width: 480px) {
            .orders-table thead .material-symbols-outlined {
                display: none;
            }

            .th-content {
                gap: 6px;
            }
        }


        /* row card look */
        .orders-table tbody tr {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .orders-table tbody tr td {
            padding: 15px 10px;
            font-size: 14px;
        }

        .orders-table table tr>*:first-child {
            border-radius: 14px 0 0 14px;
        }

        .orders-table table tr>*:last-child {
            border-radius: 0 14px 14px 0;
        }

        /* 2-LINE TEXT STYLE */
        .td-title {
            font-size: 15px;
            font-weight: 600;
            color: #252525;
        }

        .td-sub {
            font-size: 13px;
            color: #9c9c9c;
            margin-top: 3px;
        }

        /* STATUS DOT COLORS */
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        .status-delivered {
            background: #34c759;
        }

        .status-pending {
            background: #ffcc00;
        }

        .status-assigned {
            background: #007aff;
        }

        .status-new {
            background: #ff3b30;
        }

        /* PAGINATION – GOOGLE ICON STYLE */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 14px 10px;
        }

        /* Prev / Next buttons */
        .page-nav {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            color: #555;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 600;
        }

        .page-nav .material-symbols-outlined {
            font-size: 20px;
        }

        /* Hover */
        .page-nav:hover:not(:disabled) {
            background: #ffffffb6;
        }

        /* Disabled */
        .page-nav:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        /* Page numbers */
        .page-numbers {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-numbers button {
            background: none;
            border: none;
            font-size: 14px;
            color: #777;
            cursor: pointer;
            padding: 2px 4px;
            transition: color 0.2s ease;
        }

        .page-numbers button:hover {
            color: #000;
        }

        .page-numbers button.active {
            color: #000;
            font-weight: 600;
        }


        /* RESPONSIVENESS */
        @media (max-width: 1024px) {
            .summary-boxes {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .date-filter {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .date-input-wrapper {
                flex-direction: column;
                align-items: stretch;
                gap: 5px;
            }

            .date-input-wrapper input[type="date"] {
                width: 100%;
                box-sizing: border-box;
            }

            .date-input-wrapper span {
                text-align: center;
                display: block;
            }

            .date-filter-box {
                justify-content: flex-start;
                width: 100%;
                padding: 8px 10px;
            }

            .formatted-date {
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {
            .summary-boxes {
                grid-template-columns: 1fr;
            }

            .order-tabs {
                gap: 10px 20px;
            }

            .tab-btn {
                font-size: 16px;
            }

            .date-filter {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {

            /* Allow the whole content to scroll vertically */
            .content-area {
                display: block;
                overflow-y: auto;
                padding: 10px 5px;
                min-height: auto;
                margin-bottom: 20px;
            }

            /* Make table container block-level so it displays correctly */
            .orders-table {
                display: block;
                width: 100%;
                flex: none;
            }

            /* Enable horizontal scroll for wide tables */
            .table-scroll {
                display: block;
                overflow-x: auto;
                overflow-y: visible;
                min-height: auto;
            }


            .orders-table table {
                width: max-content;
                min-width: 600px;
            }
        }

        /* Floating chat – Messenger style, bottom right */
        .float-chat-wrap {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1000;
            font-family: inherit;
        }

        .float-chat-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #fff;
            color: #333;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .float-chat-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.2);
        }

        .float-chat-btn .material-symbols-outlined {
            font-size: 26px;
        }

        .float-chat-btn.has-unread {
            box-shadow: 0 0 0 2px #0084ff, 0 4px 20px rgba(0, 132, 255, 0.35);
        }

        .float-chat-btn.has-unread:hover {
            box-shadow: 0 0 0 2px #0084ff, 0 6px 24px rgba(0, 132, 255, 0.45);
        }

        .float-chat-btn .chat-unread-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            background: #0084ff;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
        }

        .float-chat-btn.has-unread .chat-unread-badge {
            display: flex;
        }

        /* Chat head: when panel is hidden and someone messaged */
        .chat-head {
            position: absolute;
            right: 0;
            bottom: 68px;
            display: none;
            align-items: center;
            gap: 0;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
            padding: 4px 4px 4px 12px;
            max-width: 280px;
            cursor: pointer;
            z-index: 999;
            border: 1px solid #e4e6eb;
        }

        .chat-head.visible {
            display: flex;
        }

        .chat-head-bubble {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 8px 12px 8px 14px;
            min-width: 0;
        }

        .chat-head-name {
            font-weight: 600;
            font-size: 14px;
            color: #050505;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-head-preview {
            font-size: 13px;
            color: #65676b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 2px;
        }

        .chat-head-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .chat-head-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #e4e6eb;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .chat-head-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chat-head-avatar .material-symbols-outlined {
            font-size: 24px;
            color: #65676b;
        }

        .chat-head-dismiss {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid #ccc;
            color: #65676b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-size: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }

        .chat-head-dismiss:hover {
            background: #f0f2f5;
            color: #050505;
        }

        .chat-head-dismiss .material-symbols-outlined {
            font-size: 16px;
        }

        .float-chat-panel {
            position: absolute;
            right: 0;
            bottom: 68px;
            width: 420px;
            max-width: calc(100vw - 48px);
            height: 520px;
            max-height: calc(100vh - 120px);
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: row;
            overflow: hidden;
        }

        .float-chat-panel.open {
            display: flex;
        }

        .float-chat-panel.view-new-message .chat-conversation {
            display: none !important;
        }

        .float-chat-panel.view-conversation .chat-new-msg {
            display: none !important;
        }

        /* New message: contact list only (full panel) */
        .float-chat-panel .chat-new-msg {
            width: 100%;
            display: flex;
            flex-direction: column;
            background: #fff;
            min-width: 0;
        }

        .float-chat-panel .chat-new-msg-header {
            padding: 12px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e4e6eb;
        }

        .float-chat-panel .chat-new-msg-header .title {
            font-weight: 600;
            font-size: 15px;
            color: #050505;
        }

        .float-chat-panel .chat-new-msg-close {
            background: none;
            border: none;
            color: #65676b;
            cursor: pointer;
            padding: 4px;
            display: flex;
            border-radius: 50%;
        }

        .float-chat-panel .chat-new-msg-close:hover {
            background: #e4e6eb;
            color: #050505;
        }

        .float-chat-panel .chat-to-wrap {
            padding: 8px 10px;
            border-bottom: 1px solid #e4e6eb;
        }

        .float-chat-panel .chat-to-wrap label {
            display: block;
            font-size: 12px;
            color: #65676b;
            margin-bottom: 4px;
        }

        .float-chat-panel .chat-to-input {
            width: 100%;
            padding: 8px 10px;
            border: none;
            background: #f0f2f5;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .float-chat-panel .chat-to-input::placeholder {
            color: #65676b;
        }

        .float-chat-panel .chat-to-input:focus {
            outline: none;
            background: #e4e6eb;
        }

        .float-chat-panel .chat-customers {
            flex: 1;
            overflow-y: auto;
            padding: 4px 0;
        }

        .float-chat-panel .chat-customer-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            cursor: pointer;
            transition: background 0.15s;
        }

        .float-chat-panel .chat-customer-item:hover,
        .float-chat-panel .chat-customer-item.active {
            background: #f0f2f5;
        }

        .float-chat-panel .chat-customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e4e6eb;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .float-chat-panel .chat-customer-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .float-chat-panel .chat-customer-avatar .material-symbols-outlined {
            font-size: 22px;
            color: #65676b;
        }

        .float-chat-panel .chat-customer-name {
            font-weight: 500;
            font-size: 14px;
            color: #050505;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Right: conversation */
        .float-chat-panel .chat-conversation {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .float-chat-panel .chat-header {
            padding: 10px 12px;
            background: #fff;
            border-bottom: 1px solid #e4e6eb;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .float-chat-panel .chat-header-back {
            background: none;
            border: none;
            color: #65676b;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .float-chat-panel .chat-header-back:hover {
            background: #f0f2f5;
            color: #0084ff;
        }

        .float-chat-panel .chat-header-back .material-symbols-outlined {
            font-size: 24px;
        }

        .float-chat-panel .chat-header-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e4e6eb;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .float-chat-panel .chat-header-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .float-chat-panel .chat-header-avatar .material-symbols-outlined {
            font-size: 20px;
            color: #65676b;
        }

        .float-chat-panel .chat-header-name {
            flex: 1;
            color: #050505;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .float-chat-panel .chat-header-caret {
            font-size: 20px;
            color: #65676b;
            flex-shrink: 0;
        }

        .float-chat-panel .chat-header-actions {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .float-chat-panel .chat-header-close:hover {
            color: #d23f3f;
        }

        .float-chat-panel .chat-header-actions button {
            background: none;
            border: none;
            color: #65676b;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .float-chat-panel .chat-header-actions button:hover {
            background: #f0f2f5;
            color: #0084ff;
        }

        .float-chat-panel .chat-header-actions .material-symbols-outlined {
            font-size: 20px;
        }

        .float-chat-panel .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: #f0f2f5;
        }

        .float-chat-panel .chat-msg {
            max-width: 75%;
            padding: 10px 14px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
        }

        .float-chat-panel .chat-msg.admin {
            align-self: flex-end;
            background: #0084ff;
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .float-chat-panel .chat-msg.customer {
            align-self: flex-start;
            background: #e4e6eb;
            color: #050505;
            border-bottom-left-radius: 4px;
        }

        .float-chat-panel .chat-msg-time {
            font-size: 11px;
            opacity: 0.85;
            margin-top: 2px;
        }

        .float-chat-panel .chat-msg.customer .chat-msg-time {
            color: #65676b;
        }

        .float-chat-panel .chat-msg-img .chat-msg-image {
            display: block;
            max-width: 220px;
            max-height: 220px;
            border-radius: 12px;
            margin-bottom: 4px;
        }

        .float-chat-panel .chat-placeholder {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #65676b;
            font-size: 14px;
            padding: 24px;
        }

        /* Input bar – Messenger style */
        .float-chat-panel .chat-input-wrap {
            padding: 10px 12px;
            background: #fff;
            border-top: 1px solid #e4e6eb;
            display: flex;
            gap: 6px;
            align-items: flex-end;
        }

        .float-chat-panel .chat-input-actions {
            display: flex;
            align-items: center;
            gap: 2px;
            flex-shrink: 0;
        }

        .float-chat-panel .chat-input-actions button {
            background: none;
            border: none;
            color: #65676b;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .float-chat-panel .chat-input-actions button:hover {
            background: #f0f2f5;
            color: #0084ff;
        }

        .float-chat-panel .chat-input-actions .material-symbols-outlined {
            font-size: 22px;
        }

        .float-chat-panel .chat-input {
            flex: 1;
            padding: 10px 14px;
            border: none;
            background: #f0f2f5;
            border-radius: 20px;
            font-size: 15px;
            resize: none;
            min-height: 40px;
            max-height: 120px;
        }

        .float-chat-panel .chat-input::placeholder {
            color: #65676b;
        }

        .float-chat-panel .chat-input:focus {
            outline: none;
        }

        .float-chat-panel .chat-input-right {
            display: flex;
            align-items: center;
            gap: 2px;
            flex-shrink: 0;
        }

        .float-chat-panel .chat-input-right button {
            background: none;
            border: none;
            color: #65676b;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .float-chat-panel .chat-input-right button:hover {
            background: #f0f2f5;
            color: #0084ff;
        }

        .float-chat-panel .chat-input-right .chat-send {
            color: #0084ff;
        }

        .float-chat-panel .chat-input-right .chat-send:hover {
            color: #0066cc;
        }

        .float-chat-panel .chat-input-right .material-symbols-outlined {
            font-size: 22px;
        }

        @media (max-width: 480px) {
            .float-chat-wrap {
                right: 16px;
                bottom: 16px;
            }

            .float-chat-panel {
                width: calc(100vw - 32px);
                right: -8px;
                bottom: 64px;
                height: 480px;
            }

            .float-chat-panel .chat-new-msg {
                width: 120px;
                min-width: 100px;
            }
        }
    </style>
</head>

<body>
    @section('content')
        <div class="content-area">

            <!-- SUMMARY BOXES -->
            <div class="summary-boxes">
                <div class="summary-box">
                    <h4>Total Orders</h4>
                    <h2>{{ $totalOrders ?? 0 }} <span class="icon" style="background:#3b82f6"></span></h2>
                    <p>Last month: {{ $totalLastMonth ?? 0 }}</p>
                </div>

                <div class="summary-box">
                    <h4>Assigned Orders</h4>
                    <h2>{{ $assignedCount ?? 0 }} <span class="icon" style="background:#3b82f6"></span></h2>
                    <p>Last month: {{ $assignedLastMonth ?? 0 }}</p>
                </div>

                <div class="summary-box">
                    <h4>Pending Orders</h4>
                    <h2>{{ $pendingCount ?? 0 }} <span class="icon" style="background:#f59e0b"></span></h2>
                    <p>Last month: {{ $pendingLastMonth ?? 0 }}</p>
                </div>

                <div class="summary-box">
                    <h4>Delivered Orders</h4>
                    <h2>{{ $deliveredCount ?? 0 }} <span class="icon" style="background:#22c55e"></span></h2>
                    <p>Last month: {{ $deliveredLastMonth ?? 0 }}</p>
                </div>
            </div>

            <!-- TABS -->
            <div class="order-tabs">
                <button class="tab-btn active">All Orders</button>
                <button class="tab-btn">New Orders</button>
                <button class="tab-btn">Assigned Orders</button>
                <button class="tab-btn">Pending Orders</button>
                <button class="tab-btn">Delivered Orders</button>
            </div>

            <!-- DATE FILTER -->
            <div class="date-filter">
                <div class="date-input-wrapper">
                    <input type="date" id="startDate" />
                    <span>to</span>
                    <input type="date" id="endDate" />
                </div>
                <div class="date-filter-box" id="dateDisplayBox">
                    <span class="material-symbols-outlined">calendar_today</span>
                    <span id="dateFormatted" class="formatted-date"></span>
                </div>
            </div>



            <!-- ORDERS TABLE -->
            <div class="orders-table">
                <!-- Scrollable area -->
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">deployed_code</span>
                                        Transaction ID
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">person</span>
                                        Customer Name
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">event_available</span>
                                        Delivery Schedule
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">icecream</span>
                                        Product Name
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">payments</span>
                                        Product Price
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">android_cell_4_bar</span>
                                        Status
                                    </span>
                                </th>
                            </tr>
                        </thead>


                        <tbody>
                            @php
                                $orders = $orders ?? collect();
                            @endphp
                            @forelse($orders as $order)
                                @php
                                    $createdAt = $order->created_at ? \Carbon\Carbon::parse($order->created_at) : null;
                                    $deliveryDate = $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date) : null;
                                    $deliveryTime = $order->delivery_time ? \Carbon\Carbon::parse($order->delivery_time) : null;
                                    $orderDateStr = $createdAt ? $createdAt->format('Y-m-d') : '';
                                    $deliverySchedule = $deliveryDate ? $deliveryDate->format('d M Y') . ($deliveryTime ? ', ' . $deliveryTime->format('h:i A') : '') : '—';
                                    $status = strtolower($order->status ?? 'pending');
                                    $displayStatus = $status;
                                    if ($status === 'pending' && $createdAt) {
                                        $displayStatus = $createdAt->gte(now()->subMinutes(5)) ? 'new' : 'pending';
                                    }
                                    $statusClass = 'status-pending';
                                    if ($displayStatus === 'delivered') $statusClass = 'status-delivered';
                                    elseif ($displayStatus === 'assigned') $statusClass = 'status-assigned';
                                    elseif ($displayStatus === 'new') $statusClass = 'status-new';
                                    $statusLabel = $displayStatus === 'new' ? 'New' : ucfirst($displayStatus);
                                @endphp
                                <tr data-status="{{ $displayStatus }}" data-order-date="{{ $orderDateStr }}">
                                    <td>
                                        <div class="td-title">#{{ $order->transaction_id }}</div>
                                        <div class="td-sub">{{ $createdAt ? $createdAt->format('d M Y') : '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="td-title">{{ $order->customer_name ?? '—' }}</div>
                                        <div class="td-sub">{{ $order->customer_phone ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="td-title">{{ $deliverySchedule }}</div>
                                        <div class="td-sub">{{ $order->delivery_address ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="td-title">{{ $order->product_name ?? '—' }}</div>
                                        <div class="td-sub">{{ ($order->product_type ?? '') . ($order->gallon_size ? ' (' . $order->gallon_size . ')' : '') ?: '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="td-title">₱{{ number_format((float)($order->amount ?? 0), 0) }}</div>
                                        <div class="td-sub">{{ $order->payment_method ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <span class="status-dot {{ $statusClass }}"></span>
                                        <span class="td-title">{{ $statusLabel }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #888;">No orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="showingText" style="text-align:center; margin:10px 0; color:#555; font-size:14px; display:none;">
                </div>



                <!-- Pagination always visible at bottom -->
                <div class="pagination">
                    <button id="prevBtn" class="page-nav">
                        <span class="material-symbols-outlined">arrow_left_alt</span>
                        <span class="nav-text">Previous</span>
                    </button>

                    <div id="pageNumbers" class="page-numbers"></div>

                    <button id="nextBtn" class="page-nav">
                        <span class="nav-text">Next</span>
                        <span class="material-symbols-outlined">arrow_right_alt</span>
                    </button>
                </div>


            </div>

            <!-- Floating chat: Messenger-style Admin ↔ Customer -->
            <div class="float-chat-wrap" id="floatChatWrap">
                <div class="float-chat-panel view-new-message" id="floatChatPanel" aria-hidden="true">
                    <!-- Left: New message / contacts -->
                    <div class="chat-new-msg">
                        <div class="chat-new-msg-header">
                            <span class="title">New message</span>
                            <button type="button" class="chat-new-msg-close" id="floatChatClose" aria-label="Close">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                        <div class="chat-to-wrap">
                            <label for="chatToInput">To:</label>
                            <input type="text" class="chat-to-input" id="chatToInput" placeholder="Search customers" />
                        </div>
                        <div class="chat-customers" id="chatCustomerList">
                            <div class="chat-placeholder chat-loading" id="chatCustomerListPlaceholder" style="padding:16px;max-height:none;">Loading customers…</div>
                        </div>
                    </div>
                    <!-- Message: conversation (after selecting a contact) -->
                    <div class="chat-conversation">
                        <div class="chat-header" id="chatConvHeader">
                            <button type="button" class="chat-header-back" id="chatBackToNewMsg" aria-label="Back to New message">
                                <span class="material-symbols-outlined">arrow_back</span>
                            </button>
                            <div class="chat-header-avatar" id="chatHeaderAvatar">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <span class="chat-header-name" id="chatHeaderName">Select a customer</span>
                            <span class="chat-header-caret material-symbols-outlined" aria-hidden="true">keyboard_arrow_down</span>
                            <div class="chat-header-actions">
                                <button type="button" aria-label="Minimize"><span class="material-symbols-outlined">remove</span></button>
                                <button type="button" class="chat-header-close" id="chatConvClose" aria-label="Close"><span class="material-symbols-outlined">close</span></button>
                            </div>
                        </div>
                        <div class="chat-messages" id="chatMessages">
                            <div class="chat-placeholder" id="chatPlaceholder">Select a customer to view messages.</div>
                        </div>
                        <div class="chat-input-wrap">
                            <div class="chat-input-actions">
                                <input type="file" id="chatFileInput" accept="image/*" multiple hidden />
                                <button type="button" id="chatAttachBtn" aria-label="Attach images"><span class="material-symbols-outlined">image</span></button>
                            </div>
                            <textarea class="chat-input" id="chatInput" placeholder="Aa" rows="1"></textarea>
                            <div class="chat-input-right">
                                <button type="button" class="chat-thumbs" id="chatThumbs" aria-label="Like"><span class="material-symbols-outlined">thumb_up</span></button>
                                <button type="button" class="chat-send" id="chatSend" aria-label="Send"><span class="material-symbols-outlined">send</span></button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Chat head: shows when panel is hidden and there is a new message -->
                <div class="chat-head" id="chatHead" aria-hidden="true">
                    <div class="chat-head-bubble">
                        <span class="chat-head-name" id="chatHeadName">Customer</span>
                        <span class="chat-head-preview" id="chatHeadPreview">New message</span>
                    </div>
                    <div class="chat-head-avatar-wrap">
                        <div class="chat-head-avatar" id="chatHeadAvatar">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                        <button type="button" class="chat-head-dismiss" id="chatHeadDismiss" aria-label="Dismiss">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                </div>
                <button type="button" class="float-chat-btn" id="floatChatBtn" aria-label="New message">
                    <span class="chat-unread-badge" id="chatUnreadBadge">0</span>
                    <span class="material-symbols-outlined">edit</span>
                </button>
            </div>
        </div>
    @endsection
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tbody = document.querySelector(".orders-table tbody");
            const rows = Array.from(tbody.querySelectorAll("tr[data-status]"));
            const pageNumbersContainer = document.getElementById("pageNumbers");
            const prevBtn = document.getElementById("prevBtn");
            const nextBtn = document.getElementById("nextBtn");
            const pagination = document.querySelector(".pagination");
            const showingText = document.getElementById("showingText");

            const rowsPerPage = 10;
            let currentPage = 1;
            const totalRows = rows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);

            // ✔ RULE: If total rows <= 10 → show text, hide pagination
            if (totalRows <= rowsPerPage) {
                pagination.style.display = "none";

                // show “Showing X out of 10”
                showingText.style.display = "block";
                showingText.textContent = `Showing ${totalRows} data`;

                // show all rows
                rows.forEach(row => row.style.display = "");
                return; // Stop here — no need to run pagination code
            }

            showingText.style.display = "none";
            pagination.style.display = "flex";

            function renderPageButtons() {
                pageNumbersContainer.innerHTML = "";
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement("button");
                    btn.textContent = i;
                    btn.className = i === currentPage ? "active" : "";
                    btn.addEventListener("click", () => {
                        currentPage = i;
                        displayRows(currentPage);
                    });
                    pageNumbersContainer.appendChild(btn);
                }
            }

            function displayRows(page) {
                const start = (page - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                rows.forEach((row, idx) => {
                    row.style.display = idx >= start && idx < end ? "" : "none";
                });

                prevBtn.disabled = page === 1;
                nextBtn.disabled = page === totalPages;

                const btns = pageNumbersContainer.querySelectorAll("button");
                btns.forEach((b, idx) => {
                    b.classList.toggle("active", idx + 1 === page);
                });

                document.querySelector(".table-scroll").scrollTop = 0;
            }

            prevBtn.addEventListener("click", () => {
                if (currentPage > 1) {
                    currentPage--;
                    displayRows(currentPage);
                }
            });

            nextBtn.addEventListener("click", () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    displayRows(currentPage);
                }
            });

            document.addEventListener("dashboardFiltersApplied", function() {
                const visible = rows.filter(r => r.style.display !== "none");
                const total = visible.length;
                const pages = Math.max(1, Math.ceil(total / rowsPerPage));
                currentPage = currentPage > pages ? pages : currentPage;
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                const toShow = visible.slice(start, end);
                rows.forEach(r => { r.style.display = toShow.includes(r) ? "" : "none"; });
                pageNumbersContainer.innerHTML = "";
                for (let i = 1; i <= pages; i++) {
                    const btn = document.createElement("button");
                    btn.textContent = i;
                    btn.className = i === currentPage ? "active" : "";
                    const pageNum = i;
                    btn.addEventListener("click", () => { currentPage = pageNum; document.dispatchEvent(new CustomEvent("dashboardFiltersApplied")); });
                    pageNumbersContainer.appendChild(btn);
                }
                prevBtn.disabled = currentPage === 1;
                nextBtn.disabled = currentPage === pages;
                showingText.style.display = total <= rowsPerPage && total > 0 ? "block" : "none";
                showingText.textContent = total <= rowsPerPage && total > 0 ? "Showing " + total + " data" : (total === 0 ? "No data to show" : "");
                pagination.style.display = total > rowsPerPage ? "flex" : "none";
            });

            renderPageButtons();
            displayRows(currentPage);
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const rows = document.querySelectorAll(".orders-table tbody tr[data-status]");
            const searchInput = document.querySelector(".search-bar input");
            const tabButtons = document.querySelectorAll(".tab-btn");
            const startDateInput = document.getElementById("startDate");
            const endDateInput = document.getElementById("endDate");

            function formatDateReadable(dateString) {
                if (!dateString) return "";
                const d = new Date(dateString);
                const day = d.getDate();
                const month = d.toLocaleString("en-US", { month: "short" });
                const year = d.getFullYear();
                return `${day} ${month}, ${year}`;
            }

            function updateDateDisplay() {
                const start = document.getElementById("startDate").value;
                const end = document.getElementById("endDate").value;
                const display = document.getElementById("dateFormatted");
                if (start && end) {
                    display.textContent = `${formatDateReadable(start)} to ${formatDateReadable(end)}`;
                } else if (start) {
                    display.textContent = formatDateReadable(start);
                } else if (end) {
                    display.textContent = formatDateReadable(end);
                } else {
                    display.textContent = "";
                }
            }

            if (startDateInput) startDateInput.addEventListener("change", updateDateDisplay);
            if (endDateInput) endDateInput.addEventListener("change", updateDateDisplay);

            function getTabStatusFilter(activeTabText) {
                const t = activeTabText.trim();
                if (t === "All Orders") return null;
                if (t === "New Orders") return "new";
                if (t === "Assigned Orders") return "assigned";
                if (t === "Pending Orders") return "pending";
                if (t === "Delivered Orders") return "delivered";
                return null;
            }

            function applyFilters() {
                const searchText = (searchInput && searchInput.value) ? searchInput.value.toLowerCase().trim() : "";
                const activeTab = document.querySelector(".tab-btn.active");
                const activeTabText = activeTab ? activeTab.textContent.trim() : "All Orders";
                const statusFilter = getTabStatusFilter(activeTabText);
                const startDate = startDateInput && startDateInput.value ? startDateInput.value : "";
                const endDate = endDateInput && endDateInput.value ? endDateInput.value : "";

                rows.forEach(row => {
                    const rowStatus = (row.getAttribute("data-status") || "").toLowerCase();
                    const orderDate = row.getAttribute("data-order-date") || "";
                    const rowText = row.textContent.toLowerCase();

                    let matchesSearch = !searchText || rowText.includes(searchText);
                    let matchesTab = !statusFilter || rowStatus === statusFilter;
                    let matchesDate = true;
                    if (startDate && orderDate < startDate) matchesDate = false;
                    if (endDate && orderDate > endDate) matchesDate = false;

                    row.style.display = (matchesSearch && matchesTab && matchesDate) ? "" : "none";
                });

                // Re-run pagination logic so "visible" rows are repaginated (optional: trigger custom event)
                const paginationEvent = new CustomEvent("dashboardFiltersApplied");
                document.dispatchEvent(paginationEvent);
            }

            if (searchInput) searchInput.addEventListener("keyup", applyFilters);
            if (startDateInput) startDateInput.addEventListener("change", applyFilters);
            if (endDateInput) endDateInput.addEventListener("change", applyFilters);

            tabButtons.forEach(btn => {
                btn.addEventListener("click", () => {
                    tabButtons.forEach(b => b.classList.remove("active"));
                    btn.classList.add("active");
                    applyFilters();
                });
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const wrap = document.getElementById("floatChatWrap");
            const panel = document.getElementById("floatChatPanel");
            const btn = document.getElementById("floatChatBtn");
            const closeBtn = document.getElementById("floatChatClose");
            const chatInput = document.getElementById("chatInput");
            const chatSend = document.getElementById("chatSend");
            const chatMessages = document.getElementById("chatMessages");
            const chatPlaceholder = document.getElementById("chatPlaceholder");
            const chatCustomerList = document.getElementById("chatCustomerList");
            const chatCustomerListPlaceholder = document.getElementById("chatCustomerListPlaceholder");
            const chatHeaderName = document.getElementById("chatHeaderName");
            const chatHeaderAvatar = document.getElementById("chatHeaderAvatar");
            const chatToInput = document.getElementById("chatToInput");
            const chatThumbs = document.getElementById("chatThumbs");
            const convHeader = document.getElementById("chatConvHeader");
            const chatHead = document.getElementById("chatHead");
            const chatHeadName = document.getElementById("chatHeadName");
            const chatHeadPreview = document.getElementById("chatHeadPreview");
            const chatHeadAvatar = document.getElementById("chatHeadAvatar");
            const chatHeadDismiss = document.getElementById("chatHeadDismiss");
            const chatUnreadBadge = document.getElementById("chatUnreadBadge");

            const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
            const chatCustomersUrl = "{{ route('admin.chat.customers') }}";
            const chatCustomerShowUrl = "{{ url('admin/chat/customers') }}";
            const chatSendUrl = "{{ url('admin/chat/customers') }}";

            if (!panel || !btn) return;

            let selectedCustomerId = null;
            let selectedCustomerName = null;
            let unreadCount = 0;
            let searchDebounce = null;

            function getHeaders() {
                return {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };
            }

            function loadCustomers(q) {
                if (!chatCustomerList) return;
                if (!chatCustomerListPlaceholder) return;
                chatCustomerListPlaceholder.style.display = 'block';
                chatCustomerListPlaceholder.textContent = 'Loading customers…';
                chatCustomerList.querySelectorAll('.chat-customer-item').forEach(function(el) { el.remove(); });
                const url = q ? chatCustomersUrl + '?q=' + encodeURIComponent(q) : chatCustomersUrl;
                fetch(url, { headers: getHeaders(), credentials: 'same-origin' })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        chatCustomerListPlaceholder.style.display = 'none';
                        if (!data.success || !data.data || !data.data.length) {
                            chatCustomerListPlaceholder.textContent = 'No customers found.';
                            chatCustomerListPlaceholder.style.display = 'block';
                            return;
                        }
                        data.data.forEach(function(c) {
                            const item = document.createElement('div');
                            item.className = 'chat-customer-item';
                            item.setAttribute('data-customer-id', c.id);
                            item.setAttribute('data-customer-name', c.full_name || '');
                            item.setAttribute('tabindex', '0');
                            const avatarHtml = c.image_url
                                ? '<img src="' + escapeHtml(c.image_url) + '" alt="" />'
                                : '<span class="material-symbols-outlined">person</span>';
                            item.innerHTML = '<div class="chat-customer-avatar">' + avatarHtml + '</div><div class="chat-customer-name">' + escapeHtml(c.full_name || 'Customer') + '</div>';
                            item.addEventListener('click', function() { selectCustomer(c.id); });
                            chatCustomerList.appendChild(item);
                        });
                    })
                    .catch(function() {
                        chatCustomerListPlaceholder.textContent = 'Failed to load customers.';
                        chatCustomerListPlaceholder.style.display = 'block';
                    });
            }

            function selectCustomer(customerId) {
                selectedCustomerId = customerId;
                chatMessages.querySelectorAll('.chat-msg').forEach(function(m) { m.remove(); });
                if (chatPlaceholder) {
                    chatPlaceholder.textContent = 'Loading messages…';
                    chatPlaceholder.style.display = 'flex';
                }
                fetch(chatCustomerShowUrl + '/' + customerId, { headers: getHeaders(), credentials: 'same-origin' })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (!data.success) return;
                        const customer = data.customer || {};
                        var fname = (customer.firstname || '').trim();
                        var lname = (customer.lastname || '').trim();
                        selectedCustomerName = (customer.full_name || (fname + ' ' + lname).trim() || customer.email || 'Customer');
                        if (chatHeaderName) {
                            chatHeaderName.textContent = selectedCustomerName;
                            chatHeaderName.title = selectedCustomerName;
                        }
                        if (chatHeaderAvatar) {
                            var imgUrl = customer.image_url;
                            if (imgUrl) {
                                chatHeaderAvatar.innerHTML = '<img src="' + escapeHtml(String(imgUrl)) + '" alt="" />';
                            } else {
                                chatHeaderAvatar.innerHTML = '<span class="material-symbols-outlined">person</span>';
                            }
                        }
                        chatCustomerList.querySelectorAll('.chat-customer-item').forEach(function(i) {
                            i.classList.toggle('active', parseInt(i.getAttribute('data-customer-id'), 10) === customerId);
                        });
                        if (chatPlaceholder) {
                            chatPlaceholder.style.display = (data.messages && data.messages.length) ? 'none' : 'flex';
                            chatPlaceholder.textContent = (data.messages && data.messages.length) ? '' : 'No messages yet. Say hi!';
                        }
                        (data.messages || []).forEach(function(m) {
                            appendMessage(m);
                        });
                        panel.classList.remove('view-new-message');
                        panel.classList.add('view-conversation');
                        if (chatInput) chatInput.focus();
                    })
                    .catch(function() {
                        if (chatPlaceholder) {
                            chatPlaceholder.textContent = 'Failed to load messages.';
                            chatPlaceholder.style.display = 'flex';
                        }
                    });
            }

            function appendMessage(m) {
                if (chatPlaceholder) chatPlaceholder.style.display = 'none';
                const isAdmin = (m.sender_type || '') === 'admin';
                const timeStr = m.created_at ? new Date(m.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '';
                const div = document.createElement('div');
                div.className = 'chat-msg ' + (isAdmin ? 'admin' : 'customer');
                if (m.image_url) {
                    div.classList.add('chat-msg-img');
                    div.innerHTML = '<img src="' + escapeHtml(m.image_url) + '" alt="Image" class="chat-msg-image" /><div class="chat-msg-time">' + timeStr + '</div>';
                } else {
                    div.innerHTML = '<span>' + escapeHtml(m.body || '') + '</span><div class="chat-msg-time">' + timeStr + '</div>';
                }
                chatMessages.appendChild(div);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            function updateUnreadUI() {
                if (chatUnreadBadge) chatUnreadBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                if (btn) {
                    if (unreadCount > 0) btn.classList.add('has-unread');
                    else btn.classList.remove('has-unread');
                }
            }

            function hideChatHead() {
                if (chatHead) {
                    chatHead.classList.remove('visible');
                    chatHead.setAttribute('aria-hidden', 'true');
                }
            }

            function showChatHead(name, preview) {
                if (chatHeadName) chatHeadName.textContent = name || 'Customer';
                if (chatHeadPreview) chatHeadPreview.textContent = (preview || 'New message').substring(0, 40);
                if (chatHead) {
                    chatHead.classList.add('visible');
                    chatHead.setAttribute('aria-hidden', 'false');
                }
            }

            function openChat() {
                panel.classList.add('open');
                panel.setAttribute('aria-hidden', 'false');
                panel.classList.remove('view-conversation');
                panel.classList.add('view-new-message');
                unreadCount = 0;
                updateUnreadUI();
                hideChatHead();
                loadCustomers(chatToInput ? chatToInput.value.trim() : '');
            }

            function closeChat() {
                panel.classList.remove('open');
                panel.setAttribute('aria-hidden', 'true');
                if (unreadCount > 0) showChatHead(chatHeadName ? chatHeadName.textContent : '', chatHeadPreview ? chatHeadPreview.textContent : '');
            }

            btn.addEventListener('click', function() {
                if (panel.classList.contains('open')) closeChat();
                else openChat();
            });

            if (closeBtn) closeBtn.addEventListener('click', closeChat);

            if (chatHeadDismiss) {
                chatHeadDismiss.addEventListener('click', function(e) {
                    e.stopPropagation();
                    unreadCount = 0;
                    updateUnreadUI();
                    hideChatHead();
                });
            }

            if (chatHead) {
                chatHead.addEventListener('click', function(e) {
                    if (e.target.closest('.chat-head-dismiss')) return;
                    openChat();
                });
            }

            if (convHeader) {
                var minBtn = convHeader.querySelector("button[aria-label='Minimize']");
                if (minBtn) minBtn.addEventListener('click', closeChat);
            }

            var chatConvClose = document.getElementById('chatConvClose');
            if (chatConvClose) chatConvClose.addEventListener('click', closeChat);

            if (chatToInput && chatCustomerList) {
                chatToInput.addEventListener('input', function() {
                    clearTimeout(searchDebounce);
                    var q = (chatToInput.value || '').trim();
                    searchDebounce = setTimeout(function() { loadCustomers(q); }, 300);
                });
            }

            var chatBackToNewMsg = document.getElementById('chatBackToNewMsg');
            if (chatBackToNewMsg) {
                chatBackToNewMsg.addEventListener('click', function() {
                    panel.classList.remove('view-conversation');
                    panel.classList.add('view-new-message');
                });
            }

            function onIncomingMessage(senderName, messagePreview) {
                unreadCount++;
                if (chatHeadName) chatHeadName.textContent = senderName || 'Customer';
                if (chatHeadPreview) chatHeadPreview.textContent = (messagePreview || 'New message').substring(0, 40);
                updateUnreadUI();
                if (!panel.classList.contains('open')) showChatHead(senderName, messagePreview);
            }

            window.adminChatIncoming = onIncomingMessage;
            document.addEventListener('chatIncoming', function(e) {
                var d = e.detail || {};
                onIncomingMessage(d.senderName || d.name, d.messagePreview || d.preview || d.message);
            });

            function escapeHtml(s) {
                var el = document.createElement('div');
                el.textContent = s;
                return el.innerHTML;
            }

            var chatAttachBtn = document.getElementById('chatAttachBtn');
            var chatFileInput = document.getElementById('chatFileInput');
            if (chatAttachBtn && chatFileInput) {
                chatAttachBtn.addEventListener('click', function() {
                    if (!selectedCustomerId) return;
                    chatFileInput.click();
                });
                chatFileInput.addEventListener('change', function() {
                    var files = chatFileInput.files;
                    if (!files || !files.length || !selectedCustomerId) return;
                    var formData = new FormData();
                    formData.append('_token', csrfToken);
                    for (var i = 0; i < files.length; i++) {
                        if (files[i].type.startsWith('image/')) formData.append('image', files[i]);
                    }
                    if (!formData.has('image')) return;
                    fetch(chatSendUrl + '/' + selectedCustomerId + '/messages', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData,
                        credentials: 'same-origin'
                    }).then(function(res) { return res.json(); }).then(function(data) {
                        if (data.success && data.message) addImageMessage(data.message.image_url, true);
                    });
                    chatFileInput.value = '';
                });
            }

            function addImageMessage(src, isAdmin) {
                if (chatPlaceholder) chatPlaceholder.style.display = 'none';
                var timeStr = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                var div = document.createElement('div');
                div.className = 'chat-msg chat-msg-img ' + (isAdmin ? 'admin' : 'customer');
                div.innerHTML = '<img src="' + escapeHtml(src) + '" alt="Image" class="chat-msg-image" /><div class="chat-msg-time">' + timeStr + '</div>';
                chatMessages.appendChild(div);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            if (chatThumbs) {
                chatThumbs.addEventListener('click', function() {
                    if (!selectedCustomerId) return;
                    sendMessage('👍', true);
                });
            }

            function sendMessage(text, appendOnSuccess) {
                if (!selectedCustomerId) return;
                var formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('body', text);
                fetch(chatSendUrl + '/' + selectedCustomerId + '/messages', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                    credentials: 'same-origin'
                }).then(function(res) { return res.json(); }).then(function(data) {
                    if (appendOnSuccess && data.success && data.message) appendMessage(data.message);
                });
            }

            if (chatSend && chatInput) {
                chatSend.addEventListener('click', function() {
                    var text = chatInput.value.trim();
                    if (!text) return;
                    chatInput.value = '';
                    chatInput.style.height = 'auto';
                    sendMessage(text, true);
                });
                chatInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        chatSend.click();
                    }
                });
            }
        });
    </script>


</body>

</html>

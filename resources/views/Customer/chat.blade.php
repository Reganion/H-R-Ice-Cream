<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <title>Chat – Quinjay Ice Cream</title>
</head>

<body>

    <div class="chat-page">
        <header class="chat-header">
            <a href="{{ route('customer.messages') }}" class="chat-header-back" aria-label="Back">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div class="chat-header-avatar">
                <span class="material-symbols-outlined">person</span>
            </div>
            <div class="chat-header-info">
                <span class="chat-header-name">{{ $chat->sender ?? '+639123456789' }}</span>
                <span class="chat-header-subtitle">{{ $chat->subtitle ?? 'Unknown' }}</span>
            </div>
        </header>

        <main class="chat-main">
            <div class="chat-messages">
                @foreach($messages ?? [] as $msg)
                    @if($msg->incoming ?? true)
                        <div class="chat-bubble chat-bubble-incoming">
                            <div class="chat-bubble-avatar">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <div class="chat-bubble-wrap">
                                <div class="chat-bubble-content">{{ $msg->text }}</div>
                                <span class="chat-bubble-time">{{ $msg->time }}</span>
                            </div>
                        </div>
                    @else
                        <div class="chat-bubble chat-bubble-outgoing">
                            <div class="chat-bubble-wrap">
                                <div class="chat-bubble-content">{{ $msg->text }}</div>
                                <span class="chat-bubble-time">{{ $msg->time }}</span>
                            </div>
                            <div class="chat-bubble-avatar chat-bubble-avatar-me">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </main>

        <div class="chat-input-wrap">
            <input type="text" class="chat-input" placeholder="Message" aria-label="Message" />
            <button type="button" class="chat-send" aria-label="Send">
                <span class="material-symbols-outlined">send</span>
            </button>
        </div>

        <nav class="bottom-nav" aria-label="Main navigation">
            <a href="{{ route('customer.dashboard') }}" class="nav-item">
                <span class="material-symbols-outlined nav-icon">home</span>
                <span class="nav-label">Home</span>
            </a>
            <a href="{{ route('customer.order.history') }}" class="nav-item">
                <span class="material-symbols-outlined nav-icon">shopping_bag</span>
                <span class="nav-label">Order</span>
            </a>
            <a href="{{ route('customer.favorite') }}" class="nav-item">
                <span class="material-symbols-outlined nav-icon">favorite</span>
                <span class="nav-label">Favorite</span>
            </a>
            <a href="{{ route('customer.messages') }}" class="nav-item active" aria-current="page">
                <span class="material-symbols-outlined nav-icon">chat_bubble</span>
                <span class="nav-label">Messages</span>
            </a>
        </nav>
    </div>

</body>

</html>

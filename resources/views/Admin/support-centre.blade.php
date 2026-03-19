@extends('admin.layout.layout')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    @section('title', 'Support Centre')
    <link rel="stylesheet" href="{{ asset('assets/css/Admin/support-centre.css') }}">
    <style>
        button.is-loading {
            pointer-events: none !important;
            cursor: not-allowed !important;
            opacity: 0.92 !important;
            transition: all 0.22s ease;
        }

        button.is-loading .btn-loading-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        button.is-loading .btn-spinner {
            width: 14px;
            height: 14px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            display: inline-block;
            animation: btn-spin 0.7s linear infinite;
            vertical-align: middle;
        }

        #supportSendBtn {
            transition: transform 0.18s ease, box-shadow 0.22s ease, background 0.22s ease;
        }

        #supportSendBtn.is-loading {
            min-width: 118px;
            border-radius: 999px;
            padding: 0 14px;
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            color: #fff;
            box-shadow: 0 8px 22px rgba(37, 99, 235, 0.32);
            transform: translateY(-1px);
        }

        #supportSendBtn.is-loading .btn-spinner {
            border-color: rgba(255, 255, 255, 0.95);
            border-right-color: rgba(255, 255, 255, 0.2);
            filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.35));
        }

        @keyframes btn-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    @section('content')
        @php
            $supportAdminAvatar = isset($adminUser) && $adminUser && !empty($adminUser->image)
                ? asset($adminUser->image)
                : asset('img/default-user.png');
        @endphp
        <div class="support-centre-page">
            <div class="support-centre-card">
                <div class="support-centre-header">
                    <h2 class="support-centre-title">Support Centre</h2>
                    <button type="button" class="support-centre-new-btn" id="supportNewMessageBtn">
                        <span class="material-symbols-outlined">add</span>
                        New Message
                    </button>
                </div>

                <div class="support-centre-body">
                    <aside class="support-sidebar">
                        <div class="support-search">
                            <div class="support-search-box">
                                <span class="material-symbols-outlined">search</span>
                                <input type="text" id="supportCustomerSearch" placeholder="Search threads or customer" />
                            </div>
                        </div>
                        <div class="support-list" id="supportCustomerList">
                            <div class="support-empty-list" id="supportCustomerEmpty">Loading customers...</div>
                        </div>
                    </aside>

                    <section class="support-conversation">
                        <div class="support-conv-header">
                            <div class="support-avatar" id="supportHeaderAvatar">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <div class="support-header-meta">
                                <div class="support-header-name" id="supportHeaderName">Select a customer</div>
                                <div class="support-header-sub" id="supportHeaderSub">No active conversation</div>
                            </div>
                        </div>

                        <div class="support-messages" id="supportMessages">
                            <div class="support-placeholder" id="supportPlaceholder">Select a customer from the left to open a conversation.</div>
                        </div>

                        <div class="support-input-wrap">
                            <input type="file" id="supportFileInput" accept="image/*" hidden />
                            <button type="button" id="supportAttachBtn" aria-label="Attach image">
                                <span class="material-symbols-outlined">image</span>
                            </button>
                            <textarea class="support-input" id="supportInput" rows="1" placeholder="Type your message here..."></textarea>
                            <button type="button" class="support-send-btn" id="supportSendBtn" aria-label="Send">
                                <span class="material-symbols-outlined">send</span>
                            </button>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    @endsection
</body>

</html>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customerListEl = document.getElementById('supportCustomerList');
        const customerEmptyEl = document.getElementById('supportCustomerEmpty');
        const customerSearchEl = document.getElementById('supportCustomerSearch');
        const headerNameEl = document.getElementById('supportHeaderName');
        const headerSubEl = document.getElementById('supportHeaderSub');
        const headerAvatarEl = document.getElementById('supportHeaderAvatar');
        const messagesEl = document.getElementById('supportMessages');
        const placeholderEl = document.getElementById('supportPlaceholder');
        const inputEl = document.getElementById('supportInput');
        const sendBtn = document.getElementById('supportSendBtn');
        const attachBtn = document.getElementById('supportAttachBtn');
        const fileInput = document.getElementById('supportFileInput');
        const newMessageBtn = document.getElementById('supportNewMessageBtn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        if (!customerListEl || !messagesEl || !sendBtn || !inputEl) return;

        const customersUrl = "{{ route('admin.chat.customers') }}";
        const customerShowBaseUrl = "{{ url('admin/chat/customers') }}";
        const sendBaseUrl = "{{ url('admin/chat/customers') }}";
        const firebaseDbUrl = window.FIREBASE_DATABASE_URL || '';
        const adminAvatarUrl = @json($supportAdminAvatar);

        let selectedCustomerId = null;
        let selectedCustomerName = '';
        let selectedCustomerAvatar = '';
        let lastMessageId = 0;
        let searchTimer = null;
        let allCustomers = [];
        let firebaseMessagesRef = null;
        let firebaseChatsRef = null;
        let isSendingMessage = false;
        let customersRefreshTimer = null;
        let customersRealtimeRefreshTimer = null;
        let selectedConversationPollTimer = null;

        function setButtonLoadingState(button, isLoading, loadingText) {
            if (!button) return;

            if (isLoading) {
                if (button.classList.contains('is-loading')) return;
                button.dataset.originalHtml = button.innerHTML;
                button.classList.add('is-loading');
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                const text = loadingText || button.dataset.loadingText || 'Processing...';
                button.innerHTML =
                    '<span class="btn-loading-wrap"><span class="btn-spinner" aria-hidden="true"></span><span>' +
                    text + '</span></span>';
                return;
            }

            button.classList.remove('is-loading');
            button.disabled = false;
            button.removeAttribute('aria-busy');
            if (button.dataset.originalHtml) {
                button.innerHTML = button.dataset.originalHtml;
                delete button.dataset.originalHtml;
            }
        }

        function setChatSendingState(isSending) {
            isSendingMessage = isSending;
            setButtonLoadingState(sendBtn, isSending, 'Sending...');
            if (attachBtn) {
                attachBtn.disabled = isSending;
                attachBtn.style.pointerEvents = isSending ? 'none' : '';
                attachBtn.style.opacity = isSending ? '0.75' : '';
            }
            if (inputEl) {
                inputEl.readOnly = isSending;
            }
        }

        function escapeHtml(value) {
            const el = document.createElement('div');
            el.textContent = value == null ? '' : String(value);
            return el.innerHTML;
        }

        function requestHeaders() {
            return {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
        }

        function shortTime(value) {
            if (!value) return '';
            try {
                return new Date(value).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                return '';
            }
        }

        function showPlaceholder(message) {
            if (!placeholderEl) return;
            placeholderEl.style.display = 'block';
            placeholderEl.textContent = message || 'No messages yet.';
        }

        function hidePlaceholder() {
            if (placeholderEl) placeholderEl.style.display = 'none';
        }

        function resetHeader() {
            selectedCustomerName = '';
            selectedCustomerAvatar = '';
            headerNameEl.textContent = 'Select a customer';
            headerSubEl.textContent = 'No active conversation';
            headerAvatarEl.innerHTML = '<span class="material-symbols-outlined">person</span>';
        }

        function setHeader(customer) {
            const firstName = (customer?.firstname || '').trim();
            const lastName = (customer?.lastname || '').trim();
            selectedCustomerName = (customer?.full_name || (firstName + ' ' + lastName).trim() || customer?.email || 'Customer');
            selectedCustomerAvatar = customer?.image_url || '';
            headerNameEl.textContent = selectedCustomerName;
            headerSubEl.textContent = 'Thread ID: ' + (customer?.id || selectedCustomerId || '');
            headerAvatarEl.innerHTML = selectedCustomerAvatar
                ? '<img src="' + escapeHtml(selectedCustomerAvatar) + '" alt="" />'
                : '<span class="material-symbols-outlined">person</span>';
        }

        function appendMessage(message) {
            if (!message) return;
            const messageId = message.id ? String(message.id) : '';
            if (messageId && messagesEl.querySelector('[data-message-id="' + messageId + '"]')) return;

            hidePlaceholder();
            const isAdmin = (message.sender_type || '') === 'admin';
            const row = document.createElement('div');
            row.className = 'support-msg-row ' + (isAdmin ? 'admin' : 'customer');
            if (messageId) row.setAttribute('data-message-id', messageId);

            const item = document.createElement('div');
            item.className = 'support-msg ' + (isAdmin ? 'admin' : 'customer');
            if (messageId) item.setAttribute('data-message-id', messageId);

            const avatar = document.createElement('div');
            avatar.className = 'support-msg-avatar';
            const avatarUrl = isAdmin
                ? (message.admin_image_url || message.sender_image_url || adminAvatarUrl || '')
                : (message.sender_image_url || selectedCustomerAvatar || '');
            avatar.innerHTML = avatarUrl
                ? '<img src="' + escapeHtml(avatarUrl) + '" alt="" />'
                : '<span class="material-symbols-outlined">person</span>';

            const timeText = shortTime(message.created_at);
            if (message.image_url) {
                item.innerHTML = '<img src="' + escapeHtml(message.image_url) + '" alt="Image" class="support-msg-image" />' +
                    '<div class="support-msg-time">' + escapeHtml(timeText) + '</div>';
            } else {
                item.innerHTML = '<span>' + escapeHtml(message.body || '') + '</span>' +
                    '<div class="support-msg-time">' + escapeHtml(timeText) + '</div>';
            }

            if (isAdmin) {
                row.appendChild(item);
                row.appendChild(avatar);
            } else {
                row.appendChild(avatar);
                row.appendChild(item);
            }

            messagesEl.appendChild(row);
            messagesEl.scrollTop = messagesEl.scrollHeight;
            if (message.id && Number(message.id) > lastMessageId) {
                lastMessageId = Number(message.id);
            }
        }

        function renderMessages(messages) {
            messagesEl.querySelectorAll('.support-msg-row').forEach(function(node) {
                node.remove();
            });
            lastMessageId = 0;

            if (!Array.isArray(messages) || messages.length === 0) {
                showPlaceholder('No messages yet. Start the conversation.');
                return;
            }

            messages.forEach(function(message) {
                appendMessage(message);
            });
        }

        function stopFirebaseMessages() {
            if (firebaseMessagesRef) {
                firebaseMessagesRef.off();
                firebaseMessagesRef = null;
            }
        }

        function listenRealtimeMessages(customerId) {
            stopFirebaseMessages();
            if (!firebaseDbUrl || typeof firebase === 'undefined' || !firebase.database || !customerId) return;

            try {
                if (!firebase.apps.length) {
                    firebase.initializeApp({ databaseURL: firebaseDbUrl });
                }
                firebaseMessagesRef = firebase.database().ref('chats/' + customerId + '/messages');
                const upsertFromSnapshot = function(snapshot) {
                    if (!selectedCustomerId || String(selectedCustomerId) !== String(customerId)) return;
                    const key = snapshot.key;
                    const value = snapshot.val();
                    if (!key || !value) return;

                    appendMessage({
                        id: Number(key) || key,
                        sender_type: value.sender_type || 'customer',
                        body: value.body || '',
                        image_url: value.image_url || '',
                        created_at: value.created_at || ''
                    });
                };
                firebaseMessagesRef.on('child_added', upsertFromSnapshot);
                firebaseMessagesRef.on('child_changed', upsertFromSnapshot);
            } catch (e) {
                console.warn('Support centre realtime listener failed', e);
            }
        }

        function stopConversationPolling() {
            if (selectedConversationPollTimer) {
                clearInterval(selectedConversationPollTimer);
                selectedConversationPollTimer = null;
            }
        }

        function startConversationPolling(customerId) {
            stopConversationPolling();
            if (!customerId) return;
            selectedConversationPollTimer = setInterval(function() {
                if (document.hidden || !selectedCustomerId || String(selectedCustomerId) !== String(customerId)) return;
                fetch(customerShowBaseUrl + '/' + customerId, { headers: requestHeaders(), credentials: 'same-origin' })
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (!data || !data.success || !Array.isArray(data.messages)) return;
                        data.messages.forEach(function(message) { appendMessage(message); });
                    })
                    .catch(function() { /* ignore realtime fallback errors */ });
            }, 3000);
        }

        function markActiveCustomer() {
            customerListEl.querySelectorAll('.support-customer-item').forEach(function(item) {
                item.classList.toggle('active', String(item.getAttribute('data-customer-id')) === String(selectedCustomerId || ''));
            });
        }

        function renderCustomerList(customers) {
            customerListEl.querySelectorAll('.support-customer-item').forEach(function(node) {
                node.remove();
            });

            if (!Array.isArray(customers) || customers.length === 0) {
                customerEmptyEl.style.display = 'block';
                customerEmptyEl.textContent = 'No customers found.';
                return;
            }

            customerEmptyEl.style.display = 'none';

            customers.forEach(function(customer) {
                const item = document.createElement('div');
                item.className = 'support-customer-item';
                item.setAttribute('data-customer-id', customer.id);

                const avatarHtml = customer.image_url
                    ? '<img src="' + escapeHtml(customer.image_url) + '" alt="" />'
                    : '<span class="material-symbols-outlined">person</span>';
                const unreadRaw = customer.unread_count ?? customer.unread ?? customer.unread_messages ?? 0;
                const unreadCount = Number.parseInt(unreadRaw, 10);
                const safeUnreadCount = Number.isFinite(unreadCount) && unreadCount > 0 ? unreadCount : 0;
                const unreadHtml = safeUnreadCount > 0
                    ? '<span class="support-item-unread">' + (safeUnreadCount > 99 ? '99+' : safeUnreadCount) + '</span>'
                    : '';
                const preview = customer.last_message_preview || 'No message yet';
                const time = customer.last_message_at ? shortTime(customer.last_message_at) : '';

                item.innerHTML = '' +
                    '<div class="support-avatar">' + avatarHtml + '</div>' +
                    '<div class="support-item-main">' +
                    '  <div class="support-item-top">' +
                    '    <span class="support-item-name">' + escapeHtml(customer.full_name || 'Customer') + '</span>' +
                    '    <span class="support-item-time">' + escapeHtml(time) + '</span>' +
                    '  </div>' +
                    '  <div class="support-item-preview-row">' +
                    '    <span class="support-item-preview">' + escapeHtml(preview) + '</span>' +
                    unreadHtml +
                    '  </div>' +
                    '</div>';

                item.addEventListener('click', function() {
                    selectCustomer(customer.id);
                });
                customerListEl.appendChild(item);
            });

            markActiveCustomer();
        }

        function loadCustomers(query) {
            customerEmptyEl.style.display = 'block';
            customerEmptyEl.textContent = 'Loading customers...';
            const url = query ? (customersUrl + '?q=' + encodeURIComponent(query)) : customersUrl;

            fetch(url, { headers: requestHeaders(), credentials: 'same-origin' })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (!data.success) {
                        customerEmptyEl.style.display = 'block';
                        customerEmptyEl.textContent = 'Failed to load customers.';
                        return;
                    }
                    allCustomers = Array.isArray(data.data) ? data.data : [];
                    renderCustomerList(allCustomers);
                    const unreadConversations = allCustomers.reduce(function(sum, customer) {
                        const raw = customer.unread_count ?? customer.unread ?? customer.unread_messages ?? 0;
                        const value = Number.parseInt(raw, 10);
                        return sum + ((Number.isFinite(value) && value > 0) ? 1 : 0);
                    }, 0);
                    document.dispatchEvent(new CustomEvent('support:unread-conversations-updated', {
                        detail: { count: unreadConversations }
                    }));
                })
                .catch(function() {
                    customerEmptyEl.style.display = 'block';
                    customerEmptyEl.textContent = 'Failed to load customers.';
                });
        }

        function listenRealtimeCustomerList() {
            if (!firebaseDbUrl || typeof firebase === 'undefined' || !firebase.database) return;
            try {
                if (!firebase.apps.length) {
                    firebase.initializeApp({ databaseURL: firebaseDbUrl });
                }
                firebaseChatsRef = firebase.database().ref('chats');
                const scheduleRefresh = function() {
                    if (customersRealtimeRefreshTimer) clearTimeout(customersRealtimeRefreshTimer);
                    customersRealtimeRefreshTimer = setTimeout(function() {
                        loadCustomers(customerSearchEl ? customerSearchEl.value.trim() : '');
                    }, 250);
                };
                firebaseChatsRef.on('child_added', scheduleRefresh);
                firebaseChatsRef.on('child_changed', scheduleRefresh);
                firebaseChatsRef.on('child_removed', scheduleRefresh);
            } catch (e) {
                console.warn('Support centre customer realtime listener failed', e);
            }
        }

        function selectCustomer(customerId) {
            if (!customerId) return;
            selectedCustomerId = Number(customerId);
            lastMessageId = 0;
            showPlaceholder('Loading messages...');
            markActiveCustomer();

            fetch(customerShowBaseUrl + '/' + selectedCustomerId, { headers: requestHeaders(), credentials: 'same-origin' })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (!data.success) {
                        showPlaceholder('Failed to load conversation.');
                        return;
                    }
                    setHeader(data.customer || {});
                    renderMessages(data.messages || []);
                    listenRealtimeMessages(selectedCustomerId);
                    startConversationPolling(selectedCustomerId);
                })
                .catch(function() {
                    showPlaceholder('Failed to load conversation.');
                });
        }

        function sendMessage(text) {
            if (!selectedCustomerId) return;
            const body = (text || '').trim();
            if (!body) return;
            if (isSendingMessage) return;

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('body', body);
            setChatSendingState(true);

            fetch(sendBaseUrl + '/' + selectedCustomerId + '/messages', {
                method: 'POST',
                headers: requestHeaders(),
                body: formData,
                credentials: 'same-origin'
            })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success && data.message) {
                        appendMessage(data.message);
                        inputEl.value = '';
                        inputEl.style.height = 'auto';
                        loadCustomers(customerSearchEl ? customerSearchEl.value.trim() : '');
                    }
                })
                .catch(function() {
                    /* ignore send errors */
                })
                .finally(function() {
                    setChatSendingState(false);
                });
        }

        function sendImage(file) {
            if (!selectedCustomerId || !file || !file.type || !file.type.startsWith('image/')) return;
            if (isSendingMessage) return;

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('image', file);
            setChatSendingState(true);

            fetch(sendBaseUrl + '/' + selectedCustomerId + '/messages', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
                credentials: 'same-origin'
            })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success && data.message) {
                        appendMessage(data.message);
                        loadCustomers(customerSearchEl ? customerSearchEl.value.trim() : '');
                    }
                })
                .catch(function() {
                    /* ignore send errors */
                })
                .finally(function() {
                    setChatSendingState(false);
                });
        }

        if (sendBtn) {
            sendBtn.addEventListener('click', function() {
                sendMessage(inputEl.value);
            });
        }

        if (inputEl) {
            inputEl.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    sendMessage(inputEl.value);
                }
            });
        }

        if (attachBtn && fileInput) {
            attachBtn.addEventListener('click', function() {
                if (!selectedCustomerId) return;
                fileInput.click();
            });
            fileInput.addEventListener('change', function() {
                const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
                sendImage(file);
                fileInput.value = '';
            });
        }

        if (customerSearchEl) {
            customerSearchEl.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    loadCustomers(customerSearchEl.value.trim());
                }, 280);
            });
        }

        if (newMessageBtn) {
            newMessageBtn.addEventListener('click', function() {
                selectedCustomerId = null;
                stopFirebaseMessages();
                stopConversationPolling();
                resetHeader();
                markActiveCustomer();
                renderMessages([]);
                showPlaceholder('Select a customer from the left to open a conversation.');
            });
        }

        // Keep sidebar unread badges in sync with incoming messages.
        customersRefreshTimer = setInterval(function() {
            if (document.hidden) return;
            loadCustomers(customerSearchEl ? customerSearchEl.value.trim() : '');
        }, 8000);

        listenRealtimeCustomerList();
        loadCustomers('');
        resetHeader();
        showPlaceholder('Select a customer from the left to open a conversation.');
    });
</script>

// chat.js - Chat functionality JavaScript

// Global variables
let currentListingId = null;
let currentReceiverId = null;
let messageInterval = null;

// Global DOM references (set later)
let messageContainer, messageInput, chatHeader;

// Load chat messages
function loadChatMessages() {
    if (!currentListingId || !currentReceiverId) return;

    fetch(`../controllers/get_chat_messages.php?listing_id=${currentListingId}&user_id=${currentReceiverId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                messageContainer.innerHTML = `<div class="error-message">${data.error}</div>`;
                return;
            }

            if (data.listing) {
                chatHeader.innerHTML = `
                    <div class="back-button" onclick="backToChatList()">←</div>
                    <div class="chat-header-content">
                        <div class="chat-header-title">${data.listing.item_name}</div>
                        <div class="chat-header-price">R${data.listing.price}</div>
                    </div>
                `;
            }

            if (data.messages.length === 0) {
                messageContainer.innerHTML = '<div class="no-messages">No messages yet. Start the conversation!</div>';
                return;
            }

            let html = '';
            let previousDate = '';

            data.messages.forEach(message => {
                const messageDate = new Date(message.created_at);
                const formattedDate = messageDate.toLocaleDateString();

                if (formattedDate !== previousDate) {
                    html += `<div class="date-separator">${formattedDate}</div>`;
                    previousDate = formattedDate;
                }
                // console.log("message.sender_id:" + message.sender_id);
                //  console.log("currentReceiverId:" + currentReceiverId);
                

                console.log("START OF MESSAGE CLASS");
                const formattedTime = messageDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const messageClass = Number(message.sender_id) === Number(currentReceiverId) ? 'received' : 'sent';
                // console.log("Type of message.sender_id:", typeof message.sender_id);
                // console.log("Type of currentReceiverId:", typeof currentReceiverId);

                const answered = message.sender_id === currentReceiverId;
                // console.log("answered:" + answered);
                // console.log("messageClass:" + messageClass);
                // console.log("END OF MESSAGE CLASS");
                html += `
                    <div class="message ${messageClass}">
                        <div class="message-content">${message.message}</div>
                        <div class="message-time">${formattedTime}</div>
                    </div>
                `;
            });

            messageContainer.innerHTML = html;
            messageContainer.scrollTop = messageContainer.scrollHeight;
        })
        .catch(error => {
            console.error('Error loading messages:', error);
            messageContainer.innerHTML = '<div class="error-message">Failed to load messages</div>';
        });
}


// Open chat globally
function openChat(listingId, userId) {
    currentListingId = listingId;
    currentReceiverId = userId;

    const url = new URL(window.location);
    url.searchParams.set('listing_id', listingId);
    url.searchParams.set('user_id', userId);
    window.history.pushState({}, '', url);

    document.querySelector('.chat-container').classList.add('chat-open');
    if (window.innerWidth <= 768) {
        document.querySelector('.chat-list').style.display = 'none';
    }

    if (messageInterval) clearInterval(messageInterval);
    loadChatMessages();
    messageInterval = setInterval(loadChatMessages, 5000);
}

// Back to chat list globally
function backToChatList() {
    document.querySelector('.chat-container').classList.remove('chat-open');
    document.querySelector('.chat-list').style.display = 'block';

    const url = new URL(window.location);
    url.searchParams.delete('listing_id');
    url.searchParams.delete('user_id');
    window.history.pushState({}, '', url);

    if (messageInterval) {
        clearInterval(messageInterval);
        messageInterval = null;
    }
}

// DOMContentLoaded main init
document.addEventListener('DOMContentLoaded', function () {
    const chatList = document.getElementById('chat-list');
    const messageForm = document.getElementById('message-form');
    chatHeader = document.getElementById('chat-header');
    messageInput = document.getElementById('message-input');
    messageContainer = document.getElementById('message-container');

    // Read params and open chat or show list
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('listing_id') && urlParams.has('user_id')) {
        const listingId = urlParams.get('listing_id');
        const userId = urlParams.get('user_id');
        openChat(listingId, userId);
    } else {
        loadChatList();
    }

    // Submit message
    if (messageForm) {
        messageForm.addEventListener('submit', function (e) {
            e.preventDefault();
            sendMessage();
        });
    }

    // Load user chat list
    function loadChatList() {
        fetch('../controllers/get_user_chats.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    chatList.innerHTML = `<div class="error-message">${data.error}</div>`;
                    return;
                }

                if (data.chats.length === 0) {
                    chatList.innerHTML = '<div class="no-chats">No conversations yet</div>';
                    return;
                }

                let html = '';
                data.chats.forEach(chat => {
                    const unreadBadge = chat.unread_count > 0 ?
                        `<span class="unread-badge">${chat.unread_count}</span>` : '';

                    html += `
                        <div class="chat-item" onclick="openChat(${chat.listing_id}, ${chat.other_user_id})">
                            <div class="chat-item-title">
                                <strong>${chat.item_name}</strong>
                                <span class="chat-price">R${chat.price}</span>
                            </div>
                            <div class="chat-item-user">
                                <span>with ${chat.other_user_name}</span>
                                ${unreadBadge}
                            </div>
                            <div class="chat-item-preview">${chat.last_message || 'No messages yet'}</div>
                        </div>
                    `;
                });

                chatList.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading chats:', error);
                chatList.innerHTML = '<div class="error-message">Failed to load chats</div>';
            });
    }

    // Send a message
    function sendMessage() {
        if (!currentListingId || !currentReceiverId) return;

        const message = messageInput.value.trim();
        if (!message) return;

        messageInput.value = '';

        const data = {
            listing_id: currentListingId,
            receiver_id: currentReceiverId,
            message: message
        };

        fetch('../controllers/send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error sending message:', data.error);
                    return;
                }

                loadChatMessages();
            })
            .catch(error => {
                console.error('Error sending message:', error);
            });
    }
});

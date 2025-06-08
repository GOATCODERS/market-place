// seller_chat_enhancement.js - Additional functions for seller chat features

document.addEventListener('DOMContentLoaded', function() {
    // Check if we're in chat view from seller perspective
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('listing_id') && urlParams.has('user_id')) {
        const listingId = urlParams.get('listing_id');
        const buyerId = urlParams.get('user_id');
        
        // Mark messages as read
        markMessagesAsRead(listingId, buyerId);
    }
    
    // Add buyer info to chat header
    function enhanceChatHeader(buyerName) {
        const headerContent = document.querySelector('.chat-header-content');
        if (headerContent) {
            const buyerInfo = document.createElement('div');
            buyerInfo.className = 'chat-header-buyer';
            buyerInfo.innerHTML = `Chatting with: <strong>${buyerName}</strong>`;
            headerContent.appendChild(buyerInfo);
        }
    }
    
    // Mark messages as read
    function markMessagesAsRead(listingId, buyerId) {
        fetch(`../controllers/mark_read.php?listing_id=${listingId}&buyer_id=${buyerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error marking messages as read:', data.error);
                    return;
                }
                
                // Update unread badge in navbar if it exists
                const unreadBadge = document.querySelector('.nav-unread-badge');
                if (unreadBadge && data.messages_marked_read > 0) {
                    const currentCount = parseInt(unreadBadge.textContent);
                    const newCount = Math.max(0, currentCount - data.messages_marked_read);
                    
                    if (newCount > 0) {
                        unreadBadge.textContent = newCount;
                    } else {
                        unreadBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    // Add event listener to update buyer info when messages are loaded
    const originalLoadChatMessages = window.loadChatMessages;
    if (originalLoadChatMessages) {
        window.loadChatMessages = function() {
            originalLoadChatMessages();
            
            // Get buyer name from message
            setTimeout(() => {
                const receivedMessage = document.querySelector('.message.received');
                if (receivedMessage) {
                    const senderName = receivedMessage.getAttribute('data-sender');
                    if (senderName) {
                        enhanceChatHeader(senderName);
                    }
                }
            }, 500);
        };
    }
});
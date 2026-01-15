    <!-- Chatbot Widget -->
    <div class="chat-widget">
        <div class="chat-header" onclick="toggleChat()">
            💬 Chat Support
        </div>
        <div class="chat-body" id="chat-box">
            <div class="message bot-msg">Hello! I am the Fedpodam Bot. Ask me about shipping or products!</div>
        </div>
        <div class="chat-input-area">
            <input type="text" id="chat-input" placeholder="Type a message..." onkeypress="handleEnter(event)">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        // Chatbot Logic
        const chatBox = document.getElementById('chat-box');
        const chatInput = document.getElementById('chat-input');

        function toggleChat() {
            const body = document.querySelector('.chat-body');
            // Simple toggle visibility logic could go here
        }

        function handleEnter(e) {
            if (e.key === 'Enter') sendMessage();
        }

        function sendMessage() {
            const text = chatInput.value.trim();
            if (!text) return;

            // Add User Message to UI
            appendMessage(text, 'user-msg');
            chatInput.value = '';

            // Send to PHP Backend
            fetch('api.php?action=chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            })
            .then(res => res.json())
            .then(data => {
                appendMessage(data.reply, 'bot-msg');
            })
            .catch(err => {
                appendMessage("Error connecting to bot.", 'bot-msg');
            });
        }

        function appendMessage(text, className) {
            const div = document.createElement('div');
            div.className = `message ${className}`;
            div.innerText = text;
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight; // Auto scroll to bottom
        }
    </script>
</body>
</html>
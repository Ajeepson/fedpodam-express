    <footer class="site-footer">
        <div class="footer-grid">
            <!-- Company Info -->
            <div class="footer-col">
                <h3>Fedpodam Express</h3>
                <p>Your number one campus marketplace. We connect students and staff with quality products, ensuring fast and secure delivery within the Federal Polytechnic Damaturu community.</p>
            </div>

            <!-- Quick Links -->
            <div class="footer-col">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Frequently Asked Questions (FAQ)</a></li>
                    <li><a href="#">Support Center</a></li>
                    <li><a href="#">Return Policy</a></li>
                    <li><a href="dashboard.php">Track Your Order</a></li>
                </ul>
            </div>

            <!-- Contact & Map -->
            <div class="footer-col">
                <h3>Contact Us</h3>
                <p>📍 Federal Polytechnic Damaturu, Yobe State.</p>
                <p>📞 +234 800 123 4567</p>
                <p>✉️ support@fedpodam.com</p>
                <iframe class="map-frame" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3936.596839977966!2d11.9666!3d11.7500!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTHCsDQ1JzAwLjAiTiAxMcKwNTgnMDAuMCJF!5e0!3m2!1sen!2sng!4v1600000000000!5m2!1sen!2sng" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="social-links">
                <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#"><i class="fa-brands fa-whatsapp"></i></a>
                <a href="#"><i class="fa-brands fa-twitter"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-pinterest"></i></a>
                <a href="#"><i class="fa-brands fa-facebook"></i></a>
                <a href="#"><i class="fa-brands fa-github"></i></a>
            </div>
            <p>&copy; <?php echo date('Y'); ?> Fedpodam Express. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Chatbot Widget -->
    <div id="lubbyy-btn" onclick="toggleChat()">💬</div>

    <div id="lubbyy-chat">
        <div id="lubbyy-header" onclick="toggleChat()">
            Fedpodam Bot
        </div>
        <div id="lubbyy-body">
            <div style="padding:8px; background:#eee; border-radius:4px; margin-bottom:8px;">Hello! I am the Fedpodam Bot. Ask me about shipping or products!</div>
        </div>
        <div id="lubbyy-input">
            <input type="text" id="chat-input" placeholder="Type a message..." style="background-color:#1F2937" onkeypress="handleEnter(event)">
        </div>
    </div>

    <script>
        // Chatbot Logic
        const chatBox = document.getElementById('lubbyy-body');
        const chatInput = document.getElementById('chat-input');
        const chatWindow = document.getElementById('lubbyy-chat');

        function toggleChat() {
            if (chatWindow.style.display === 'flex') {
                chatWindow.style.display = 'none';
            } else {
                chatWindow.style.display = 'flex';
            }
        }

        function handleEnter(e) {
            if (e.key === 'Enter') sendMessage();
        }

        function sendMessage() {
            const text = chatInput.value.trim();
            if (!text) return;

            // Add User Message to UI
            appendMessage(text, 'user');
            chatInput.value = '';

            // Send to PHP Backend
            fetch('api/api.php?action=chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            })
            .then(res => res.json())
            .then(data => {
                appendMessage(data.reply, 'bot');
            })
            .catch(err => {
                appendMessage("Error connecting to bot.", 'bot');
            });
        }

        function appendMessage(text, sender) {
            const div = document.createElement('div');
            div.style.padding = '8px';
            div.style.borderRadius = '4px';
            div.style.marginBottom = '8px';
            div.style.background = sender === 'user' ? '#1e3a8a' : '#eee';
            div.style.color = sender === 'user' ? '#fff' : '#333';
            div.style.alignSelf = sender === 'user' ? 'flex-end' : 'flex-start';
            div.style.maxWidth = '80%';
            div.innerText = text;
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight; // Auto scroll to bottom
        }
    </script>
</body>
</html>
const express = require('express');
const cors = require('cors');
const path = require('path');
const bodyParser = require('body-parser');
require('dotenv').config();

const { Configuration, OpenAIApi } = require('openai');
const OPENAI_KEY = process.env.OPENAI_API_KEY || '';
let openai = null;
if (OPENAI_KEY) {
  const configuration = new Configuration({ apiKey: OPENAI_KEY });
  openai = new OpenAIApi(configuration);
}

const app = express();
app.use(cors());
app.use(bodyParser.json());

// Serve frontend
app.use(express.static(path.join(__dirname, '..', 'public')));

// -- Simple in-memory product catalogue (sample)
const products = [
  { id: 1, name: 'Leather Shoes', category: 'shoes', price: 4500, img: 'images/shoes1.jpg', desc: 'Classic leather shoes.' },
  { id: 2, name: 'Palm Sandal', category: 'shoes', price: 1200, img: 'images/sandal1.jpg', desc: 'Comfortable palm sandal.' },
  { id: 3, name: 'Traditional Cap', category: 'caps', price: 800, img: 'images/cap1.jpg', desc: 'Embroidered traditional cap.' },
  { id: 4, name: 'Interlock Block', category: 'building', price: 150, img: 'images/block1.jpg', desc: 'Durable interlock block.' },
  { id: 5, name: 'Ceramic Tile', category: 'tiles', price: 2500, img: 'images/tile1.jpg', desc: 'Polished ceramic tile.' },
  { id: 6, name: 'Liquid Hand Wash', category: 'cosmetics', price: 950, img: 'images/handwash1.jpg', desc: 'Gentle liquid hand wash.' }
];

app.get('/api/products', (req, res) => {
  const { q, category } = req.query;
  let out = products;
  if (category) out = out.filter(p => p.category === category);
  if (q) out = out.filter(p => p.name.toLowerCase().includes(q.toLowerCase()));
  res.json(out);
});

app.get('/api/products/:id', (req, res) => {
  const id = Number(req.params.id);
  const p = products.find(x => x.id === id);
  if (!p) return res.status(404).json({ error: 'Product not found' });
  res.json(p);
});

// Chat endpoint used by frontend Lubbyy. If OPENAI_API_KEY is present, this proxies to OpenAI.
app.post('/api/chat', async (req, res) => {
  const { message } = req.body;
  if (!message) return res.status(400).json({ error: 'No message' });

  // If OpenAI is configured, call it
  if (openai) {
    try {
      const response = await openai.createChatCompletion({
        model: 'gpt-3.5-turbo',
        messages: [
          { role: 'system', content: 'You are Lubbyy, a friendly shopping assistant for FEDPODAM Express. Keep answers short and helpful.' },
          { role: 'user', content: message }
        ],
        max_tokens: 200
      });

      const reply = response.data.choices[0].message.content.trim();
      return res.json({ reply });
    } catch (err) {
      console.error('OpenAI error', err.message || err);
      // fall through to fallback
    }
  }

  // Fallback rule-based assistant
  const lower = message.toLowerCase();
  let reply = "I'm Lubbyy — I can help you find products, check categories, and answer simple questions. Try: 'show shoes' or 'how to buy'.";
  if (lower.includes('show') && lower.includes('shoe')) reply = 'Try our Shoes category: /shop.html?q=shoes';
  else if (lower.includes('show') && lower.includes('cap')) reply = 'Check Traditional Caps under the Caps category: /shop.html?category=caps';
  else if (lower.includes('delivery')) reply = 'We deliver within the city in 2–4 days. Delivery fees depend on order value.';
  else if (lower.includes('price') && lower.includes('tile')) reply = 'Tiles start from ₦2500 — see /shop.html?category=tiles';

  res.json({ reply });
});

// Port
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`Server running on port ${PORT}`));

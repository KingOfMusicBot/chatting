# 🤖 Human-Like AI Telegram Bot (PHP)

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Groq](https://img.shields.io/badge/AI-Groq_Llama3-f55036?style=for-the-badge)
![MongoDB](https://img.shields.io/badge/Database-MongoDB_Atlas-47A248?style=for-the-badge&logo=mongodb&logoColor=white)
![Heroku](https://img.shields.io/badge/Deploy-Heroku-430098?style=for-the-badge&logo=heroku&logoColor=white)

A high-performance, context-aware Telegram Chatbot built with **PHP**. It uses **Groq API (Llama 3)** for ultra-fast AI responses and **MongoDB Atlas** to remember conversation history, making interactions feel genuinely human-like.

---

## 🚀 Features

* **⚡ Ultra Fast:** Powered by Groq Cloud (Llama 3-8b model) for instant replies.
* **🧠 Context Aware:** Remembers past conversations using MongoDB (stores last 10 messages).
* **💬 Human Touch:** Displays "Typing..." status before sending a reply.
* **📝 Hinglish Support:** Configured via System Prompt to speak in natural Hinglish/English.
* **☁️ Cloud Ready:** Optimized for Heroku with 1-Click Deployment.

---

## 🛠️ Prerequisites

Before you begin, make sure you have:

1.  **Telegram Bot Token:** Get it from [@BotFather](https://t.me/BotFather).
2.  **Groq API Key:** Get a free key from [Groq Cloud Console](https://console.groq.com/).
3.  **MongoDB Connection String:** Create a free cluster on [MongoDB Atlas](https://www.mongodb.com/atlas).
    * *Important:* Whitelist IP `0.0.0.0/0` in MongoDB Network Access settings.

---

## 🚀 Deployment Options

### Option 1: One-Click Deploy (Recommended)

Deploy this bot to Heroku without touching a single line of code.

<a href="https://heroku.com/deploy?template=https://github.com/KingOfMusicBot/chatting">
  <img src="https://www.herokucdn.com/deploy/button.svg" alt="Deploy">
</a>

> **Note:** Replace `YOUR_GITHUB_USERNAME` and `YOUR_REPO_NAME` in the link above after pushing to GitHub.

### Option 2: Manual Heroku Deployment

If you prefer the command line:

```bash
# Clone the repository
git clone https://github.com/KingOfMusicBot/chatting
cd your-repo

# Login to Heroku
heroku login

# Create App
heroku create your-app-name

# Set Environment Variables
heroku config:set TELEGRAM_TOKEN="your_token_here"
heroku config:set GROQ_API_KEY="your_api_key"
heroku config:set MONGO_URI="mongodb+srv://..."

# Deploy
git push heroku master

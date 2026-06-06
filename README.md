# QuantumApi SDK - Quick Start Guide

Welcome to the **QuantumApi SDK**. This guide will walk you through the process of generating, installing, and securing your game SDK to ensure seamless communication with our high-speed servers.

## 🚀 Step 1: Generate Your Personalized SDK

1. Open the **SDK Generator** (`index.html`) in your web browser.
2. Use the dropdown menu to select the game SDK you wish to install.
3. Enter your unique **QuantumApi Key** (e.g., `qk_live_...`).
4. Click **Build & Download ZIP**. 
5. Extract the downloaded ZIP file. *Do not rename or modify the core connection files inside.*

## ⚙️ Step 2: Installation

1. Open your game project on your server.
2. Locate the **Root Directory** of your game.
3. Copy **ALL** files from your freshly extracted SDK package.
4. Paste them directly into your game's Root Directory.

## 🔒 Step 3: Mandatory Security Whitelisting

Your SDK will **NOT WORK** until you formally whitelist your server in our database! Our network utilizes advanced Server-to-Server Origin Verification to protect your account.

1. Log into your QuantumApi Dashboard.
2. Navigate to **Domain Management / Security**.
3. Add your exact **Website Domain Name** (e.g., `yourwebsite.com`).
4. Add your exact **Server IP Address** (e.g., `104.22.33.11`).
5. Save your settings.

*Note: If an attacker ever steals your API key, they cannot use it on their own server unless their server IP matches your whitelisted IP exactly.*

---

## ⚠️ STRICT ANTI-PIRACY & COPYRIGHT WARNING ⚠️

**Copyright © QuantumApi. All Rights Reserved.**

This SDK and its internal architecture are exclusively licensed ONLY to authorized users possessing a valid QuantumApi account. Our systems actively monitor all network traffic for unauthorized usage.

**THE FOLLOWING ACTIONS ARE STRICTLY PROHIBITED:**<br>
❌ **Redistributing or Selling:** You may not sell, leak, share, or redistribute this SDK or your API key to third parties.<br>
❌ **Code Tampering:** You may not modify, reverse-engineer, decompile, or tamper with the core connection scripts to bypass licensing, domain validation, IP validation, or security mechanisms.<br>
❌ **Copyright Infringement:** Removing, altering, or obscuring the copyright notices from the code or the API payloads.<br>
❌ **Multi-Server Abuse:** Attempting to use a single licensed SDK installation across unauthorized domains or unwhitelisted servers.

**Violators caught engaging in piracy or code theft will face immediate consequences, including:**
- Instant, permanent revocation of all associated API Keys.
- Blacklisting of your Domain and Server IP across the entire QuantumApi network.
- Account termination without warning or refund.
- Swift legal action where applicable under intellectual property laws.

By generating, downloading, and using this SDK, you agree to comply with all QuantumApi terms, licensing requirements, and usage policies.

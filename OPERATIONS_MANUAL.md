# ItroveJewel - Operations & Deployment Manual

This guide outlines the standard operating procedures for **Publishing Updates to Existing Clients** and **Preparing Fresh USB Software for New Customers**.

---

## 📌 Table of Contents
1. [Workflow 1: Publishing Updates to Existing Clients](#workflow-1-publishing-updates-to-existing-clients)
   - [Step 1: Developer Code Changes & Testing](#step-1-developer-code-changes--testing)
   - [Step 2: Publishing the Release on the Central Server](#step-2-publishing-the-release-on-the-central-server)
   - [Step 3: How Clients Receive the 1-Click Update](#step-3-how-clients-receive-the-1-click-update)
2. [Workflow 2: Delivering Fresh Software to a New Customer](#workflow-2-delivering-fresh-software-to-a-new-customer)
   - [Step 1: Register New Client in Web Admin Portal](#step-1-register-new-client-in-web-admin-portal)
   - [Step 2: Register Pendrive Hardware Serial](#step-2-register-pendrive-hardware-serial)
   - [Step 3: Generate Fresh Portable USB Package](#step-3-generate-fresh-portable-usb-package)
   - [Step 4: Copy to Customer's USB Pendrive](#step-4-copy-to-customers-usb-pendrive)
   - [Step 5: Customer First-Time Setup & Activation](#step-5-customer-first-time-setup--activation)
3. [Safety Guarantees & Architecture Summary](#safety-guarantees--architecture-summary)

---

## Workflow 1: Publishing Updates to Existing Clients

When developers make changes (new features, UI enhancements, performance fixes), follow these steps to deploy the update to your central server so customers can upgrade with a single click.

```
+------------------+       +------------------------+       +-------------------------+
| Developer writes | ----> | Publish to Server DB   | ----> | Customer App receives   |
| & tests code     |       | php artisan publish    |       | "1-Click Upgrade" alert |
+------------------+       +------------------------+       +-------------------------+
```

### Step 1: Developer Code Changes & Testing

Before creating a release, ensure all changes are formatted and passing tests:

```powershell
# 1. Format code to project standards
vendor/bin/pint --format agent

# 2. Run automated test suite
php artisan test --compact
```

### Step 2: Publishing the Release on the Central Server

Run the `app:publish-release` Artisan command on your central server:

```powershell
php artisan app:publish-release <version> --title="<Release Title>" --notes="<Feature 1,Feature 2,Feature 3>"
```

#### Example:
```powershell
php artisan app:publish-release 1.2.0 --title="ItroveJewel v1.2.0 Luxury Billing & GST Release" --notes="Added GST Invoice PDF export,Optimized Barcode scanning speed,Auto-backup database improvements"
```

#### What this command does automatically:
1. Compresses clean application files into `storage/app/releases/itrovejewel-update-1.2.0.zip`.
2. Automatically excludes all Admin files, git logs, tests, and customer database files.
3. Computes the **SHA-256 cryptographic checksum** and file size.
4. Registers the release in the central server database (`database/admin.sqlite`).
5. Makes the update instantly available to all licensed client pendrives.

---

### Step 3: How Clients Receive the 1-Click Update

When the customer plugs their USB drive into any computer connected to the internet:

1. **Automatic Detection:**
   - The desktop app queries `{APP_URL}/api/updates/check` silently in the background.
   - If a new version exists, the header badge pulses: `v1.0.0 ➜ v1.2.0 [1-Click Upgrade]`.
   - A gold alert banner appears: **"NEW VERSION READY: v1.2.0 — Upgrade in 1-Click"**.

2. **Single-Click Upgrade:**
   - Customer clicks **"Upgrade in 1-Click"**.
   - An upgrade modal displays the release title, new features, and the Zero-Data-Loss safety guarantee.
   - The customer clicks **"Upgrade in 1-Click"**.

3. **Automated Safe Installation (Zero Data Loss):**
   - **Step 1:** The app automatically creates a safety snapshot of their local SQLite database (`data/database/database_backup_v1.0.0_*.sqlite`).
   - **Step 2:** Downloads the verified update zip package from the central server.
   - **Step 3:** Verifies the SHA-256 signature to prevent corruption or tampering.
   - **Step 4:** Extracts the updated files directly onto the USB pendrive (with strict blacklists protecting customer database, `.env`, and hardware locks).
   - **Step 5:** Automatically runs any new database migrations (`php artisan migrate --force`).
   - **Step 6:** Refreshes the application. The customer is now on `v1.2.0` with all their client data completely intact!

---

## Workflow 2: Delivering Fresh Software to a New Customer

Whenever you onboard a new jewelry client and prepare a new USB pendrive for them, follow this workflow:

```
+--------------------+     +-------------------+     +---------------------+     +--------------------+
| 1. Create Client   | --> | 2. Register USB   | --> | 3. Package Fresh    | --> | 4. Copy to USB &   |
| in Admin Portal    |     | Hardware Serial   |     | Portable Build      |     | Handover to Client |
+--------------------+     +-------------------+     +---------------------+     +--------------------+
```

### Step 1: Register New Client in Web Admin Portal

1. Open your Web Admin Portal in your browser:
   - Local: `http://172.16.2.14:8000/admin/clients`
   - Production: `https://yourdomain.com/admin/clients`
2. Click **"Add New Client"**.
3. Fill in:
   - **Shop Name:** (e.g. `Royal Jewel Emporium`)
   - **Client Name:** (e.g. `Rajesh Sharma`)
   - **Mobile Number:** (e.g. `9876543210`)
   - **Email:** (e.g. `rajesh@royaljewel.com`)
4. Click **"Save Client Record"**.

---

### Step 2: Register Pendrive Hardware Serial

1. Insert the brand-new USB pendrive into your computer.
2. In the Admin Portal, open the newly created client's detail page.
3. Click **"Register Pendrive Serial"**.
4. The system will detect the physical hardware serial burned into the USB firmware chip (e.g. `SANDISK_32G_98234123`).
5. Select the pendrive and click **"Bind Pendrive to Client"**.
   - The pendrive is now permanently authorized on your central server!

---

### Step 3: Generate Fresh Portable USB Package

Run the fresh packaging command in your terminal:

```powershell
php artisan app:package-portable --fresh
```

> [!TIP]
> **Production Server URL:** If preparing for a live customer, you can specify your live production server domain so the pendrive knows where to check licenses and updates:
> ```powershell
> php artisan app:package-portable --fresh --app-url="https://yourdomain.com"
> ```

#### What this command does:
* ✅ **Wipes previous test locks:** Deletes `.usb_hardware_lock.json` so the USB opens in fresh install mode.
* ✅ **Creates fresh SQLite database:** Creates a pristine `data/database/database.sqlite` with core tables and zero test/admin records.
* ✅ **Strips all Admin code:** Removes Admin controllers, Admin database, and Admin routes.
* ✅ **Configures customer `.env`:** Sets `APP_ENABLE_ADMIN_PANEL=false` and configures `APP_URL`.
* ✅ **Audits package:** Verifies zero admin code exists.

---

### Step 4: Copy to Customer's USB Pendrive

1. Open your file explorer and go to your project directory.
2. Copy the entire folder:
   ```
   D:\ItroveJewel\USB_READY_ItroveJewel
   ```
3. Paste it directly onto the customer's USB drive (e.g. `E:\` or `F:\`).

*(Optional: You can rename the folder on the USB drive to `ItroveJewel` or leave as is).*

---

### Step 5: Customer First-Time Setup & Activation

1. The customer plugs the USB drive into ANY Windows computer.
2. They open the folder on their USB drive and double-click **`itrovejewel.exe`**.
3. Because the pendrive has no lock file yet, the **First-Time Hardware Lock Wizard** automatically appears:
   - It reads the physical USB serial from the pendrive firmware chip.
   - It verifies the serial with your central server (`/api/license/verify-usb`).
   - Displays: **"Licensed to: Royal Jewel Emporium - Rajesh Sharma"**.
4. Customer clicks **"INSTALL & LOCK TO THIS USB PENDRIVE"**.
5. The software cryptographically binds itself to that physical pendrive chip using HMAC-SHA256.
6. The full ItroveJewel workspace unlocks!

#### 🔒 Anti-Piracy Protection:
If the customer tries to copy the files to another pendrive, hard drive, or cloud storage, the app immediately blocks execution with an **Unauthorized Hardware Lockout** screen. It ONLY runs from the authorized physical USB pendrive.

---

## Safety Guarantees & Architecture Summary

| Feature | How It Is Protected |
|---|---|
| **Customer Data Protection** | SQLite database (`data/database/database.sqlite`) is 100% portable on the USB drive. Before any update, an automated backup is created. Updates never touch user database files. |
| **Offline Operation** | The app operates completely normally without internet connection. When reconnected, updates can be applied in 1-click. |
| **Dead-Man's Switch** | If the USB drive is unplugged while the software is running, the app immediately terminates within 500ms and leaves zero trace on the host PC. |
| **Admin Code Separation** | Admin portal routes, controllers, and databases are strictly server-side and never built into customer USB drives. |
